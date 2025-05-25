<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MiniLeague;
use App\Models\User;
use App\Services\MiniLeagueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MiniLeagueController extends Controller
{
    protected MiniLeagueService $miniLeagueService;

    public function __construct(MiniLeagueService $miniLeagueService)
    {
        $this->miniLeagueService = $miniLeagueService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $miniLeagues = $this->miniLeagueService->getUserMiniLeagues(auth()->user());
        return response()->json($miniLeagues);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        try {
            $miniLeague = $this->miniLeagueService->createMiniLeague(auth()->user(), $validated);
            return response()->json($miniLeague, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(MiniLeague $miniLeague): JsonResponse
    {
        if (!$miniLeague->users()->where('users.id', Auth::id())->exists()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $miniLeague->load(['creator', 'users', 'ranking']);
        return response()->json($miniLeague);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MiniLeague $miniLeague): JsonResponse
    {
        if ($miniLeague->created_by !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
        ]);

        try {
            $miniLeague = $this->miniLeagueService->updateMiniLeague($miniLeague, $validated);
            return response()->json($miniLeague);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MiniLeague $miniLeague): JsonResponse
    {
        if ($miniLeague->created_by !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $this->miniLeagueService->deleteMiniLeague($miniLeague);
            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function addMember(Request $request, MiniLeague $miniLeague): JsonResponse
    {
        if ($miniLeague->created_by !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        try {
            $user = User::findOrFail($validated['user_id']);
            $this->miniLeagueService->addMember($miniLeague, $user);
            return response()->json(['message' => 'Member added successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function removeMember(Request $request, MiniLeague $miniLeague): JsonResponse
    {
        if ($miniLeague->created_by !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        try {
            $user = User::findOrFail($validated['user_id']);
            $this->miniLeagueService->removeMember($miniLeague, $user);
            return response()->json(['message' => 'Member removed successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function leave(MiniLeague $miniLeague): JsonResponse
    {
        // Cannot leave if you're the creator
        if ($miniLeague->created_by === Auth::id()) {
            return response()->json(['error' => 'League creator cannot leave. Transfer ownership or delete the league.'], 422);
        }

        try {
            $this->miniLeagueService->leaveMiniLeague($miniLeague, auth()->user());
            return response()->json(['message' => 'Left mini league successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function getRankings(MiniLeague $miniLeague): JsonResponse
    {
        if (!$miniLeague->users()->where('users.id', Auth::id())->exists()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $rankings = $this->miniLeagueService->getRankings($miniLeague);
        return response()->json($rankings);
    }
}
