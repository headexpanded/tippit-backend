<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SiteStatistics;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class StatisticsController extends Controller
{
    public function getUserStatistics(): JsonResponse
    {
        $user = Auth::user();
        $statistics = $user->statistics;

        return response()->json($statistics);
    }

    public function getGlobalRankings(): JsonResponse
    {
        $rankings = User::with('statistics')
            ->whereHas('statistics')
            ->get()
            ->map(function ($user) {
                return [
                    'user' => $user,
                    'points' => $user->statistics->total_points,
                    'rank' => $user->statistics->current_rank,
                    'correct_predictions' => $user->statistics->correct_predictions,
                    'total_predictions' => $user->statistics->total_predictions,
                ];
            })
            ->sortByDesc('points')
            ->values();

        return response()->json($rankings);
    }

    public function getSiteStatistics(): JsonResponse
    {
        $statistics = SiteStatistics::first();

        if (!$statistics) {
            $statistics = SiteStatistics::create([
                'total_users' => User::count(),
                'active_users' => User::where('last_login_at', '>=', now()->subDays(30))->count(),
                'total_predictions' => 0,
                'total_matches' => 0,
                'total_mini_leagues' => 0,
            ]);
        }

        return response()->json($statistics);
    }

    public function getSeasonStatistics(string $season): JsonResponse
    {
        $users = User::with(['statistics', 'predictions' => function ($query) use ($season) {
            $query->whereHas('game', function ($q) use ($season) {
                $q->where('season', $season);
            });
        }])->get();

        $seasonStats = $users->map(function ($user) use ($season) {
            $seasonPredictions = $user->predictions->filter(function ($prediction) use ($season) {
                return $prediction->game->season === $season;
            });

            return [
                'user' => $user,
                'season' => $season,
                'total_predictions' => $seasonPredictions->count(),
                'correct_predictions' => $seasonPredictions->where('points', '>', 0)->count(),
                'total_points' => $seasonPredictions->sum('points'),
            ];
        })->sortByDesc('total_points')
          ->values();

        return response()->json($seasonStats);
    }

    public function getPredictionAccuracy(): JsonResponse
    {
        $user = Auth::user();
        $predictions = $user->predictions()
            ->with('game')
            ->get();

        $accuracy = [
            'total' => $predictions->count(),
            'correct' => $predictions->where('points', '>', 0)->count(),
            'exact_score' => $predictions->where('points', 3)->count(),
            'correct_result' => $predictions->where('points', 1)->count(),
            'incorrect' => $predictions->where('points', 0)->count(),
        ];

        if ($accuracy['total'] > 0) {
            $accuracy['percentage'] = round(($accuracy['correct'] / $accuracy['total']) * 100, 2);
        } else {
            $accuracy['percentage'] = 0;
        }

        return response()->json($accuracy);
    }
}
