<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\Prediction;
use App\Models\Round;
use App\Models\RoundUserStatistics;
use App\Models\User;
use App\Models\UserStatistics;
use Illuminate\Database\Seeder;

class PredictionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createPredictionsForCompletedRounds();
        $this->calculateUserStatistics();
        $this->calculateRoundUserStatistics();
        $this->calculateRankings();
    }

    /**
     * Create realistic predictions for completed rounds
     */
    private function createPredictionsForCompletedRounds(): void
    {
        $completedRounds = Round::where('is_completed', true)->get();
        $users = User::all();

        foreach ($completedRounds as $round) {
            $games = Game::where('round_id', $round->id)->get();

            foreach ($users as $user) {
                foreach ($games as $game) {
                    // 90% chance user made a prediction for this game
                    if (rand(1, 100) <= 90) {
                        $prediction = $this->generateRealisticPrediction($game, $user);

                        Prediction::create([
                            'user_id' => $user->id,
                            'game_id' => $game->id,
                            'predicted_home_score' => $prediction['home_score'],
                            'predicted_away_score' => $prediction['away_score'],
                            'points_awarded' => $this->calculatePoints($prediction, $game),
                            'created_at' => $game->lockout_time->subHours(rand(1, 24)),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Generate realistic prediction based on home advantage and user's supported team
     */
    private function generateRealisticPrediction($game, $user): array
    {
        $homeScore = rand(0, 3);
        $awayScore = rand(0, 3);

        // Home advantage (slight bias towards home team)
        if (rand(1, 100) <= 60) {
            $homeScore += rand(0, 1);
        }

        // User bias towards their supported team
        if ($user->supported_team_id) {
            if ($user->supported_team_id == $game->home_team_id) {
                $homeScore += rand(0, 1);
            } elseif ($user->supported_team_id == $game->away_team_id) {
                $awayScore += rand(0, 1);
            }
        }

        // Some users make more conservative predictions
        if (rand(1, 100) <= 30) {
            $homeScore = min(2, $homeScore);
            $awayScore = min(2, $awayScore);
        }

        return [
            'home_score' => min(5, $homeScore),
            'away_score' => min(5, $awayScore)
        ];
    }

    /**
     * Calculate points for a prediction
     */
    private function calculatePoints($prediction, $game): int
    {
        if (!$game->home_score || !$game->away_score) {
            return 0;
        }

        $predictedResult = $prediction['home_score'] > $prediction['away_score'] ? 'home' :
                          ($prediction['home_score'] < $prediction['away_score'] ? 'away' : 'draw');

        $actualResult = $game->home_score > $game->away_score ? 'home' :
                       ($game->home_score < $game->away_score ? 'away' : 'draw');

        $points = 0;

        // Correct result: 3 points
        if ($predictedResult === $actualResult) {
            $points += 3;
        }

        // Correct home team score: 2 points
        if ($prediction['home_score'] == $game->home_score) {
            $points += 2;
        }

        // Correct away team score: 2 points
        if ($prediction['away_score'] == $game->away_score) {
            $points += 2;
        }

        return $points;
    }

    /**
     * Calculate user statistics based on actual predictions
     */
    private function calculateUserStatistics(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            $predictions = Prediction::where('user_id', $user->id)
                ->whereHas('game', function ($query) {
                    $query->where('status', 'completed');
                })
                ->get();

            $totalPoints = $predictions->sum('points_awarded');
            $totalPredictions = $predictions->count();
            $correctPredictions = $predictions->where('points_awarded', '>=', 3)->count();
            $exactScorePredictions = $predictions->where('points_awarded', 7)->count(); // Perfect score (3+2+2)

            // Calculate rounds played
            $roundsPlayed = $predictions->groupBy('game.round_id')->count();

            // Get latest points (from most recent round)
            $latestPoints = 0;
            if ($predictions->isNotEmpty()) {
                $latestGame = $predictions->sortByDesc('game.round_id')->first()->game;
                $latestPoints = $predictions->where('game_id', $latestGame->id)->sum('points_awarded');
            }

            UserStatistics::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'total_points' => $totalPoints,
                    'latest_points' => $latestPoints,
                    'rounds_played' => $roundsPlayed,
                    'total_predictions' => $totalPredictions,
                    'correct_predictions' => $correctPredictions,
                    'exact_score_predictions' => $exactScorePredictions,
                    'current_rank' => 0, // Will be calculated later
                    'best_rank' => 0,
                ]
            );
        }
    }

    /**
     * Calculate round user statistics
     */
    private function calculateRoundUserStatistics(): void
    {
        $completedRounds = Round::where('is_completed', true)->get();
        $users = User::all();

        foreach ($completedRounds as $round) {
            foreach ($users as $user) {
                $roundPredictions = Prediction::where('user_id', $user->id)
                    ->whereHas('game', function ($query) use ($round) {
                        $query->where('round_id', $round->id);
                    })
                    ->get();

                if ($roundPredictions->isNotEmpty()) {
                    $pointsEarned = $roundPredictions->sum('points_awarded');
                    $predictionsMade = $roundPredictions->count();
                    $correctPredictions = $roundPredictions->where('points_awarded', '>=', 3)->count();
                    $exactScorePredictions = $roundPredictions->where('points_awarded', 7)->count(); // Perfect score

                    RoundUserStatistics::updateOrCreate(
                        ['user_id' => $user->id, 'round_id' => $round->id],
                        [
                            'points_earned' => $pointsEarned,
                            'predictions_made' => $predictionsMade,
                            'correct_predictions' => $correctPredictions,
                            'exact_score_predictions' => $exactScorePredictions,
                        ]
                    );
                }
            }
        }
    }

    /**
     * Calculate user rankings
     */
    private function calculateRankings(): void
    {
        $users = User::with('statistics')
            ->whereHas('statistics')
            ->get()
            ->sortByDesc('statistics.total_points');

        $rank = 1;
        foreach ($users as $user) {
            $user->statistics->update([
                'current_rank' => $rank,
                'best_rank' => min($rank, $user->statistics->best_rank ?: $rank)
            ]);
            $rank++;
        }
    }
}
