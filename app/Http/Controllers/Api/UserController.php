<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    protected UserService $userService;

    /**
     * @param  UserService  $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @return JsonResponse
     */
    public function profile(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        if ($user instanceof User) {
            $user->load(['statistics', 'predictions', 'miniLeagues']);
            return response()->json($user);
        }
        return response()->json(['error' => 'User not found'], 404);
    }

    /**
     * @return JsonResponse
     */
    public function getPredictions(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user instanceof User) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $predictions = $user->predictions()
            ->with(['game.homeTeam', 'game.awayTeam'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($predictions);
    }

    /**
     * @return JsonResponse
     */
    public function getLeagues(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user instanceof User) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $leagues = $user->leagues()
            ->with(['creator', 'users'])
            ->get();

        return response()->json($leagues);
    }

    /**
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        return UserResource::collection(User::all());
    }

    /**
     * @param  User  $user
     *
     * @return JsonResponse
     */
    public function show(User $user): JsonResponse
    {
        $user = $this->userService->getUserWithRelations($user);
        return response()->json($user);
    }

    /**
     * @param  User  $user
     *
     * @return JsonResponse
     */
    public function destroy(User $user): JsonResponse
    {
        $this->userService->deleteUser($user);
        return response()->json(null, 204);
    }

    /**
     * @param  Request  $request
     *
     * @return JsonResponse
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'preferences' => 'required|array',
        ]);

        /** @var User $user */
        $user = Auth::user();
        if (!$user instanceof User) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $user = $this->userService->updateUserPreferences($user, $validated['preferences']);
        return response()->json($user);
    }

    /**
     * @param  Request  $request
     *
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => 'required|string|min:2',
        ]);

        $users = $this->userService->searchUsers($validated['query']);
        return response()->json($users);
    }

    /**
     * @return JsonResponse
     */
    public function getTopUsers(): JsonResponse
    {
        $users = $this->userService->getTopUsers();
        return response()->json($users);
    }
}
