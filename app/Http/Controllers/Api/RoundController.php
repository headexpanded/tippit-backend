<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Round;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoundController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $rounds = Round::orderBy('start_date')->get();
        return response()->json($rounds);
    }

    /**
     * @param  Round  $round
     *
     * @return JsonResponse
     */
    public function show(Round $round): JsonResponse
    {
        return response()->json($round);
    }

    /**
     * @param  Round  $round
     *
     * @return JsonResponse
     */
    public function games(Round $round): JsonResponse
    {
        $games = $round->games()->with(['homeTeam', 'awayTeam'])->get();
        return response()->json($games);
    }

    /**
     * @param  Round  $round
     *
     * @return JsonResponse
     */
    public function statistics(Round $round): JsonResponse
    {
        if ($round->isFuture()) {
            return response()->json(['message' => 'Round statistics are not available yet'], 403);
        }

        $completedGames = $round->games()->where('is_completed', true)->get();

        $statistics = [
            'total_games' => $round->games()->count(),
            'completed_games' => $completedGames->count(),
            'total_goals' => $completedGames->sum(function ($game) {
                return $game->home_score + $game->away_score;
            }),
            'average_goals_per_game' => $completedGames->count() > 0 ?
                $completedGames->sum(function ($game) {
                    return $game->home_score + $game->away_score;
                }) / $completedGames->count() : 0,
        ];

        return response()->json($statistics);
    }

    /**
     * @param  Round  $round
     *
     * @return JsonResponse
     */
    public function allUsersStatistics(Round $round): JsonResponse
    {
        if ($round->isFuture()) {
            return response()->json(['message' => 'User statistics for this round are not available yet'], 403);
        }

        // With the modifications proposed earlier that created RoundUserStatistics
        $allUserStats = $round->userStatistics()
                              ->with('user:id,username')
                              ->get()
                              ->map(function ($stats) {
                                  return [
                                      'user_id' => $stats->user_id,
                                      'username' => $stats->user->username,
                                      'points_earned' => $stats->points_earned,
                                      'predictions_made' => $stats->predictions_made,
                                      'correct_predictions' => $stats->correct_predictions,
                                      'exact_score_predictions' => $stats->exact_score_predictions,
                                  ];
                              });

        return response()->json($allUserStats);
    }


    /**
     * @param  Round  $round
     * @param  User  $user
     *
     * @return JsonResponse
     */
    public function userStatistics(Round $round, User $user): JsonResponse
    {
        if ($round->isFuture()) {
            return response()->json(['message' => 'User statistics for this round are not available yet'], 403);
        }

        $statistics = [
            'total_points' => $user->predictions()
                ->whereHas('match', function ($query) use ($round) {
                    $query->where('round_id', $round->id);
                })
                ->sum('points'),
            'total_predictions' => $user->predictions()
                ->whereHas('match', function ($query) use ($round) {
                    $query->where('round_id', $round->id);
                })
                ->count(),
            'correct_predictions' => $user->predictions()
                ->whereHas('match', function ($query) use ($round) {
                    $query->where('round_id', $round->id);
                })
                ->where('points', '>', 0)
                ->count(),
        ];

        return response()->json($statistics);
    }

    /**
     * @param  Round  $round
     * @param  User  $user
     *
     * @return JsonResponse
     */
    public function userPredictions(Round $round, User $user): JsonResponse
    {
        if ($round->isFuture()) {
            return response()->json(['message' => 'Predictions for this round are not available yet'], 403);
        }

        $predictions = $user->predictions()
            ->whereHas('game', function ($query) use ($round) {
                $query->where('round_id', $round->id);
            })
            ->with(['game.homeTeam', 'game.awayTeam'])
            ->get();

        return response()->json($predictions);
    }
}
