<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Prediction;
use App\Services\PredictionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PredictionController extends Controller
{
    protected PredictionService $predictionService;

    public function __construct(PredictionService $predictionService)
    {
        $this->predictionService = $predictionService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $predictions = $this->predictionService->getUserPredictions(auth()->user());
        return response()->json($predictions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Game $game): JsonResponse
    {
        $validated = $request->validate([
            'predicted_home_score' => 'required|integer|min:0',
            'predicted_away_score' => 'required|integer|min:0',
        ]);

        try {
            $prediction = $this->predictionService->createPrediction(
                auth()->user(),
                $game,
                $validated
            );
            return response()->json($prediction, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Prediction $prediction): JsonResponse
    {
        return response()->json($prediction->load(['game.homeTeam', 'game.awayTeam']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Prediction $prediction): JsonResponse
    {
        $validated = $request->validate([
            'predicted_home_score' => 'required|integer|min:0',
            'predicted_away_score' => 'required|integer|min:0',
        ]);

        try {
            $prediction = $this->predictionService->updatePrediction($prediction, $validated);
            return response()->json($prediction);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Prediction $prediction): JsonResponse
    {
        try {
            $this->predictionService->deletePrediction($prediction);
            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function getGamePredictions(Game $game): JsonResponse
    {
        $predictions = $this->predictionService->getPredictionsForGame($game);
        return response()->json($predictions);
    }

    public function getUserPredictionForGame(Game $game): JsonResponse
    {
        $prediction = $this->predictionService->getUserPredictionForGame(auth()->user(), $game);
        return response()->json($prediction);
    }
}
