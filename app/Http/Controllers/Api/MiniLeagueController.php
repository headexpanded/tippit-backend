<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MiniLeague;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MiniLeagueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $miniLeagues = MiniLeague::with(['creator', 'users'])
            ->whereHas('users', function ($query) {
                $query->where('users.id', Auth::id());
            })
            ->get();

        return response()->json($miniLeagues);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $miniLeague = MiniLeague::create([
            'name' => $request->name,
            'description' => $request->description,
            'created_by' => Auth::id(),
        ]);

        // Add creator as first member
        $miniLeague->users()->attach(Auth::id(), ['joined_at' => now()]);

        return response()->json($miniLeague, 201);
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

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $miniLeague->update($request->all());
        return response()->json($miniLeague);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MiniLeague $miniLeague): JsonResponse
    {
        if ($miniLeague->created_by !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $miniLeague->delete();
        return response()->json(null, 204);
    }

    public function addMember(Request $request, MiniLeague $miniLeague): JsonResponse
    {
        if ($miniLeague->created_by !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if league is full
        if ($miniLeague->users()->count() >= 10) {
            return response()->json(['error' => 'Mini league is full'], 422);
        }

        // Check if user is already a member
        if ($miniLeague->users()->where('users.id', $request->user_id)->exists()) {
            return response()->json(['error' => 'User is already a member'], 422);
        }

        $miniLeague->users()->attach($request->user_id, ['joined_at' => now()]);

        return response()->json(['message' => 'Member added successfully']);
    }

    public function removeMember(Request $request, MiniLeague $miniLeague): JsonResponse
    {
        if ($miniLeague->created_by !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Cannot remove the creator
        if ($request->user_id === $miniLeague->created_by) {
            return response()->json(['error' => 'Cannot remove the league creator'], 422);
        }

        $miniLeague->users()->detach($request->user_id);

        return response()->json(['message' => 'Member removed successfully']);
    }

    public function leave(MiniLeague $miniLeague): JsonResponse
    {
        // Cannot leave if you're the creator
        if ($miniLeague->created_by === Auth::id()) {
            return response()->json(['error' => 'League creator cannot leave. Transfer ownership or delete the league.'], 422);
        }

        $miniLeague->users()->detach(Auth::id());

        return response()->json(['message' => 'Left mini league successfully']);
    }

    public function getRankings(MiniLeague $miniLeague): JsonResponse
    {
        if (!$miniLeague->users()->where('users.id', Auth::id())->exists()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $rankings = $miniLeague->users()
            ->with('statistics')
            ->get()
            ->map(function ($user) {
                return [
                    'user' => $user,
                    'points' => $user->statistics->total_points,
                    'rank' => $user->statistics->current_rank,
                ];
            })
            ->sortByDesc('points')
            ->values();

        return response()->json($rankings);
    }
}
