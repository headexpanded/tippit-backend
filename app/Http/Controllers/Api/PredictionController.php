<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Prediction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PredictionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $predictions = Prediction::with(['game.homeTeam', 'game.awayTeam'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($predictions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'game_id' => 'required|exists:games,id',
            'predicted_home_score' => 'required|integer|min:0',
            'predicted_away_score' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $game = Game::findOrFail($request->game_id);

        // Check if game is locked
        if (now()->isAfter($game->lockout_time)) {
            return response()->json(['error' => 'Game is locked. Cannot make predictions.'], 403);
        }

        // Check if prediction already exists
        $existingPrediction = Prediction::where('user_id', Auth::id())
            ->where('game_id', $request->game_id)
            ->first();

        if ($existingPrediction) {
            return response()->json(['error' => 'Prediction already exists for this game.'], 409);
        }

        $prediction = Prediction::create([
            'user_id' => Auth::id(),
            'game_id' => $request->game_id,
            'predicted_home_score' => $request->predicted_home_score,
            'predicted_away_score' => $request->predicted_away_score,
        ]);

        return response()->json($prediction, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Prediction $prediction): JsonResponse
    {
        if ($prediction->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $prediction->load(['game.homeTeam', 'game.awayTeam']);
        return response()->json($prediction);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Prediction $prediction): JsonResponse
    {
        if ($prediction->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'predicted_home_score' => 'required|integer|min:0',
            'predicted_away_score' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $game = Game::findOrFail($prediction->game_id);

        // Check if game is locked
        if (now()->isAfter($game->lockout_time)) {
            return response()->json(['error' => 'Game is locked. Cannot update prediction.'], 403);
        }

        $prediction->update([
            'predicted_home_score' => $request->predicted_home_score,
            'predicted_away_score' => $request->predicted_away_score,
        ]);

        return response()->json($prediction);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Prediction $prediction): JsonResponse
    {
        if ($prediction->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $game = Game::findOrFail($prediction->game_id);

        // Check if game is locked
        if (now()->isAfter($game->lockout_time)) {
            return response()->json(['error' => 'Game is locked. Cannot delete prediction.'], 403);
        }

        $prediction->delete();
        return response()->json(null, 204);
    }

    public function getUserPredictionsForGame(Game $game): JsonResponse
    {
        $prediction = Prediction::where('user_id', Auth::id())
            ->where('game_id', $game->id)
            ->first();

        return response()->json($prediction);
    }

    public function getPredictionsForGame(Game $game): JsonResponse
    {
        $predictions = Prediction::with('user')
            ->where('game_id', $game->id)
            ->get();

        return response()->json($predictions);
    }
}
