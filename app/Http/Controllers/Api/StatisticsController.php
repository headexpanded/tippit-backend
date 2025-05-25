<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\StatisticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    protected StatisticsService $statisticsService;

    public function __construct(StatisticsService $statisticsService)
    {
        $this->statisticsService = $statisticsService;
    }

    public function getUserStatistics(User $user): JsonResponse
    {
        $statistics = $this->statisticsService->getUserStatistics($user);
        return response()->json($statistics);
    }

    public function getGlobalRankings(): JsonResponse
    {
        $rankings = $this->statisticsService->getGlobalRankings();
        return response()->json($rankings);
    }

    public function getSiteStatistics(): JsonResponse
    {
        $statistics = $this->statisticsService->getSiteStatistics();
        return response()->json($statistics);
    }

    public function getSeasonStatistics(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'season' => 'required|string',
        ]);

        $statistics = $this->statisticsService->getSeasonStatistics($validated['season']);
        return response()->json($statistics);
    }

    public function getPredictionAccuracy(User $user): JsonResponse
    {
        $accuracy = $this->statisticsService->getPredictionAccuracy($user);
        return response()->json($accuracy);
    }
}
