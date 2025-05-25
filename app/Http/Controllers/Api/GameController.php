<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Services\GameService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GameController extends Controller
{
    protected GameService $gameService;

    public function __construct(GameService $gameService)
    {
        $this->gameService = $gameService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $games = $this->gameService->getUpcomingGames();
        return response()->json($games);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'home_team_id' => 'required|exists:teams,id',
            'away_team_id' => 'required|exists:teams,id',
            'match_date' => 'required|date',
            'match_time' => 'required',
            'season' => 'required|string',
            'lockout_time' => 'required|date',
        ]);

        $game = $this->gameService->createGame($validated);
        return response()->json($game, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Game $game): JsonResponse
    {
        return response()->json($game->load(['homeTeam', 'awayTeam']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Game $game): JsonResponse
    {
        $validated = $request->validate([
            'home_team_id' => 'exists:teams,id',
            'away_team_id' => 'exists:teams,id',
            'match_date' => 'date',
            'match_time' => 'string',
            'season' => 'string',
            'lockout_time' => 'date',
        ]);

        $game = $this->gameService->updateGame($game, $validated);
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
        $validated = $request->validate([
            'home_score' => 'required|integer|min:0',
            'away_score' => 'required|integer|min:0',
        ]);

        $game = $this->gameService->updateScore(
            $game,
            $validated['home_score'],
            $validated['away_score']
        );

        return response()->json($game);
    }

    public function completed(): JsonResponse
    {
        $games = $this->gameService->getCompletedGames();
        return response()->json($games);
    }
}
