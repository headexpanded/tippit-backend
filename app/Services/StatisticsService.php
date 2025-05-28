<?php

namespace App\Services;

use App\Models\SiteStatistics;
use App\Models\User;
use Illuminate\Support\Collection;
use JetBrains\PhpStorm\ArrayShape;

class StatisticsService extends BaseService
{
    /**
     * @param  Model  $model
     */
    public function __construct(SiteStatistics $model)
    {
        parent::__construct($model);
    }

    /**
     * @param  User  $user
     *
     * @return array|int[]
     */
    public function getUserStatistics(User $user): array
    {
        $statistics = $user->statistics;

        if (!$statistics) {
            return [
                'total_points' => 0,
                'total_predictions' => 0,
                'correct_predictions' => 0,
                'current_rank' => 0,
            ];
        }

        return [
            'total_points' => $statistics->total_points,
            'total_predictions' => $statistics->total_predictions,
            'correct_predictions' => $statistics->correct_predictions,
            'current_rank' => $statistics->current_rank,
        ];
    }

    /**
     * @return Collection
     */
    public function getGlobalRankings(): Collection
    {
        return User::with('statistics')
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
    }

    /**
     * @return SiteStatistics
     */
    public function getSiteStatistics(): SiteStatistics
    {
        $statistics = $this->model->first();

        if (!$statistics) {
            $statistics = $this->model->create([
                'total_users' => User::count(),
                'active_users' => User::where('last_login_at', '>=', now()->subDays(30))->count(),
                'total_predictions' => 0,
                'total_matches' => 0,
                'total_mini_leagues' => 0,
            ]);
        }

        return $statistics;
    }

    /**
     * @param  string  $season
     *
     * @return Collection
     */
    public function getSeasonStatistics(string $season): Collection
    {
        $users = User::with(['statistics', 'predictions' => function ($query) use ($season) {
            $query->whereHas('game', function ($q) use ($season) {
                $q->where('season', $season);
            });
        }])->get();

        return $users->map(function ($user) use ($season) {
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
    }

    /**
     * @param  User  $user
     *
     * @return array
     */
    #[ArrayShape([
        'total' => "int",
        'correct' => "int",
        'exact_score' => "int",
        'correct_result' => "int",
        'incorrect' => "int",
        'percentage' => "float|int"
    ])] public function getPredictionAccuracy(User $user): array
    {
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

        return $accuracy;
    }

    /**
     * @return void
     */
    public function updateSiteStatistics(): void
    {
        $statistics = $this->getSiteStatistics();

        $statistics->update([
            'total_users' => User::count(),
            'active_users' => User::where('last_login_at', '>=', now()->subDays(30))->count(),
            'total_predictions' => $statistics->total_predictions,
            'total_matches' => $statistics->total_matches,
            'total_mini_leagues' => $statistics->total_mini_leagues,
        ]);
    }
}
