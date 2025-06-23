<?php

namespace App\Services;

use App\Models\Round;
use App\Models\RoundUserStatistics;
use App\Models\User;

class UserStatisticsService
{
    /**
     * Update user statistics for a completed round
     *
     * @param  Round  $round
     *
     * @return void
     */
    public function updateRoundStatistics(Round $round): void
    {
        if (!$round->is_completed) {
            return;
        }

        $users = User::all();

        foreach ($users as $user) {
            $predictions = $user->predictions()
                                ->whereHas('game', function ($query) use ($round) {
                                    $query->where('round_id', $round->id);
                                })
                                ->get();

            if ($predictions->isEmpty()) {
                continue;
            }

            $correctPredictions = $predictions->filter(function ($prediction) {
                $game = $prediction->game;

                // Determine if the prediction was correct (home team won, draw, or away team won)
                $actualResult = $game->home_score > $game->away_score
                    ? 'home'
                    :
                    ($game->home_score < $game->away_score ? 'away' : 'draw');

                $predictedResult = $prediction->predicted_home_score > $prediction->predicted_away_score
                    ? 'home'
                    :
                    ($prediction->predicted_home_score < $prediction->predicted_away_score ? 'away' : 'draw');

                return $actualResult === $predictedResult;
            });

            $exactScorePredictions = $predictions->filter(function ($prediction) {
                $game = $prediction->game;

                return $prediction->predicted_home_score === $game->home_score && $prediction->predicted_away_score === $game->away_score;
            });

            // Calculate points based on correct predictions and exact scores
            $pointsEarned = ($correctPredictions->count() * 3) + ($exactScorePredictions->count() * 2);

            // Create or update round statistics for this user
            RoundUserStatistics::updateOrCreate(
                ['user_id' => $user->id, 'round_id' => $round->id],
                [
                    'points_earned' => $pointsEarned,
                    'predictions_made' => $predictions->count(),
                    'correct_predictions' => $correctPredictions->count(),
                    'exact_score_predictions' => $exactScorePredictions->count(),
                ]
            );

            // Update the user's overall statistics
            $totalRoundStats = $user->roundStatistics;
            $user->statistics()->update([
                'total_points' => $totalRoundStats->sum('points_earned'),
                'rounds_played' => $totalRoundStats->count(),
                'latest_points' => $pointsEarned,
                'total_predictions' => $totalRoundStats->sum('predictions_made'),
                'correct_predictions' => $totalRoundStats->sum('correct_predictions'),
                'exact_score_predictions' => $totalRoundStats->sum('exact_score_predictions'),
            ]);
        }
    }

    /**
     * Get all users' stats as at a given round, including ranking.
     *
     * @param int $roundId
     * @return array
     */
    public function getUsersStatsAsAtRound(int $roundId): array
    {
        $users = \App\Models\User::with(['supportedTeam', 'leagues'])->get();
        $userStats = [];

        // Get all round stats up to and including the round
        foreach ($users as $user) {
            $roundStats = $user->roundStatistics()
                ->where('round_id', '<=', $roundId)
                ->orderBy('round_id')
                ->get();

            $totalPoints = $roundStats->sum('points_earned');
            $roundsPlayed = $roundStats->count();
            $latestPoints = $roundStats->last()?->points_earned ?? 0;
            $totalPredictions = $roundStats->sum('predictions_made');
            $correctPredictions = $roundStats->sum('correct_predictions');
            $exactScorePredictions = $roundStats->sum('exact_score_predictions');
            $averagePoints = $roundsPlayed > 0 ? round($totalPoints / $roundsPlayed, 2) : 0.0;

            $userStats[] = [
                'user' => $user,
                'stats' => [
                    'totalPoints' => $totalPoints,
                    'latestPoints' => $latestPoints,
                    'roundsPlayed' => $roundsPlayed,
                    'averagePoints' => $averagePoints,
                    'currentRank' => 0, // to be filled after sorting
                    'totalPredictions' => $totalPredictions,
                    'correctPredictions' => $correctPredictions,
                    'exactScorePredictions' => $exactScorePredictions,
                ],
            ];
        }

        // Sort by totalPoints descending and assign rank
        usort($userStats, function ($a, $b) {
            return $b['stats']['totalPoints'] <=> $a['stats']['totalPoints'];
        });
        $rank = 1;
        foreach ($userStats as $i => &$entry) {
            if ($i > 0 && $entry['stats']['totalPoints'] === $userStats[$i-1]['stats']['totalPoints']) {
                $entry['stats']['currentRank'] = $userStats[$i-1]['stats']['currentRank'];
            } else {
                $entry['stats']['currentRank'] = $rank;
            }
            $rank++;
        }
        unset($entry);

        return $userStats;
    }
}
