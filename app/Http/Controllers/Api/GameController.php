<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GameController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $games = Game::with(['homeTeam', 'awayTeam'])
            ->orderBy('match_date')
            ->orderBy('match_time')
            ->get();

        return response()->json($games);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'home_team_id' => 'required|exists:teams,id',
            'away_team_id' => 'required|exists:teams,id|different:home_team_id',
            'match_date' => 'required|date',
            'match_time' => 'required',
            'season' => 'required|string',
            'lockout_time' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $game = Game::create($request->all());

        return response()->json($game, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Game $game): JsonResponse
    {
        $game->load(['homeTeam', 'awayTeam', 'predictions']);
        return response()->json($game);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Game $game): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'home_team_id' => 'exists:teams,id',
            'away_team_id' => 'exists:teams,id|different:home_team_id',
            'match_date' => 'date',
            'match_time' => 'string',
            'season' => 'string',
            'home_score' => 'nullable|integer|min:0',
            'away_score' => 'nullable|integer|min:0',
            'status' => 'in:scheduled,in_progress,completed,cancelled',
            'lockout_time' => 'date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $game->update($request->all());

        return response()->json($game);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Game $game): JsonResponse
    {
        $game->delete();
        return response()->json(null, 204);
    }

    public function updateScore(Request $request, Game $game): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'home_score' => 'required|integer|min:0',
            'away_score' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $game->update([
            'home_score' => $request->home_score,
            'away_score' => $request->away_score,
            'status' => 'completed'
        ]);

        return response()->json($game);
    }

    public function getUpcomingGames(): JsonResponse
    {
        $games = Game::with(['homeTeam', 'awayTeam'])
            ->where('status', 'scheduled')
            ->where('match_date', '>=', now()->toDateString())
            ->orderBy('match_date')
            ->orderBy('match_time')
            ->get();

        return response()->json($games);
    }

    public function getCompletedGames(): JsonResponse
    {
        $games = Game::with(['homeTeam', 'awayTeam'])
            ->where('status', 'completed')
            ->orderBy('match_date', 'desc')
            ->orderBy('match_time', 'desc')
            ->get();

        return response()->json($games);
    }
}
