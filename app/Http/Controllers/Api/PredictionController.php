<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Prediction\StorePredictionRequest;
use App\Http\Resources\PredictionResource;
use App\Models\Game;
use App\Models\Prediction;
use App\Models\User;
use App\Services\PredictionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class PredictionController extends Controller
{
    protected PredictionService $predictionService;

    /**
     * @param PredictionService $predictionService
     */
    public function __construct(PredictionService $predictionService)
    {
        $this->predictionService = $predictionService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user instanceof User) {
            return PredictionResource::collection(collect());
        }

        $predictions = $this->predictionService->getUserPredictions($user);
        return PredictionResource::collection($predictions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePredictionRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user instanceof User) {
            return response()->json(['error' => 'User not found'], 404);
        }

        try {
            $validated = $request->validated();

            $prediction = $this->predictionService->createOrUpdatePrediction(
                $user,
                $validated['game_id'],
                [
                    'predicted_home_score' => $validated['predicted_home_score'],
                    'predicted_away_score' => $validated['predicted_away_score'],
                ]
            );

            $prediction->load(['game.homeTeam', 'game.awayTeam']);

            return response()->json([
                'message' => 'Prediction saved successfully',
                'data' => new PredictionResource($prediction)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Prediction $prediction): JsonResponse
    {
        $prediction->load(['game.homeTeam', 'game.awayTeam']);

        return response()->json([
            'message' => 'Prediction retrieved successfully',
            'data' => new PredictionResource($prediction)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StorePredictionRequest $request, Prediction $prediction): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user instanceof User) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Check if user owns this prediction
        if ($prediction->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $validated = $request->validated();

            $updatedPrediction = $this->predictionService->updatePrediction($prediction, [
                'predicted_home_score' => $validated['predicted_home_score'],
                'predicted_away_score' => $validated['predicted_away_score'],
            ]);

            $updatedPrediction->load(['game.homeTeam', 'game.awayTeam']);

            return response()->json([
                'message' => 'Prediction updated successfully',
                'data' => new PredictionResource($updatedPrediction)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Prediction $prediction): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user instanceof User) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Check if user owns this prediction
        if ($prediction->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $this->predictionService->deletePrediction($prediction);
            return response()->json([
                'message' => 'Prediction deleted successfully'
            ], 204);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get all predictions for a specific game
     *
     * @param Game $game
     * @return AnonymousResourceCollection
     */
    public function getGamePredictions(Game $game): AnonymousResourceCollection
    {
        $predictions = $this->predictionService->getPredictionsForGame($game);

        return PredictionResource::collection($predictions);
    }

    /**
     * Get current user's prediction for a specific game
     *
     * @param Game $game
     * @return JsonResponse
     */
    public function getUserPredictionForGame(Game $game): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user instanceof User) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $prediction = $this->predictionService->getUserPredictionForGame($user, $game);

        if (!$prediction) {
            return response()->json([
                'message' => 'No prediction found for this game',
                'data' => null
            ], 404);
        }

        $prediction->load(['game.homeTeam', 'game.awayTeam']);

        return response()->json([
            'message' => 'User prediction retrieved successfully',
            'data' => new PredictionResource($prediction)
        ]);
    }
}
