<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\DeleteAccountRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\BasicLeagueResource;
use App\Http\Resources\PredictionResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
     * @return UserResource
     */
    public function profile(): UserResource
    {
        /** @var User $user */
        $user = Auth::user();
        if ($user instanceof User) {
            $user->load(['statistics', 'predictions', 'leagues', 'supportedTeam']);
            return new UserResource($user);
        }
        return new UserResource(new User());
    }

    /**
     * @return AnonymousResourceCollection
     */
    public function getPredictions(): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user instanceof User) {
            return PredictionResource::collection(collect());
        }

        $predictions = $user->predictions()
            ->with(['game.homeTeam', 'game.awayTeam'])
            ->orderBy('created_at', 'desc')
            ->get();

        return PredictionResource::collection($predictions);
    }

    /**
     * @return AnonymousResourceCollection
     */
    public function getLeagues(): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user instanceof User) {
            return BasicLeagueResource::collection(collect());
        }

        $leagues = $user->leagues()
            ->with(['creator', 'users'])
            ->get();

        return BasicLeagueResource::collection($leagues);
    }

    /**
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        $roundId = request()->query('roundId');
        if ($roundId) {
            $userStats = app(\App\Services\UserStatisticsService::class)->getUsersStatsAsAtRound((int)$roundId);
            // Return a collection of UserResource, passing stats as at round
            return UserResource::collection(collect($userStats)->map(function ($entry) {
                // Pass the stats as a second argument to UserResource
                return new UserResource($entry['user'], $entry['stats']);
            }));
        }
        // Default: return all users with current stats
        return UserResource::collection(User::all());
    }

    /**
     * @param  User  $user
     *
     * @return UserResource
     */
    public function show(User $user): UserResource
    {
        $user = $this->userService->getUserWithRelations($user);
        return new UserResource($user);
    }

    /**
     * Store a newly created user (admin only).
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // This method is for admin user creation, not public registration
        // Registration should use Fortify's /api/register route
        return response()->json(['error' => 'Use /api/register for user registration'], 405);
    }

    /**
     * Update the specified user (users can only update their own account).
     *
     * @param  UpdateUserRequest  $request
     * @param  User  $user
     * @return UserResource
     */
    public function update(UpdateUserRequest $request, User $user): UserResource
    {
        $validated = $request->validated();

        $user->update($validated);
        $user->load(['statistics', 'predictions', 'leagues', 'supportedTeam']);

        return new UserResource($user);
    }

    /**
     * Remove the specified user (users can only delete their own account).
     *
     * @param  User  $user
     * @return JsonResponse
     */
    public function destroy(User $user): JsonResponse
    {
        // Users can only delete their own account
        if (Auth::id() !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $this->userService->deleteUser($user);
        return response()->json(null, 204);
    }

    /**
     * Delete the authenticated user's account.
     *
     * @param  DeleteAccountRequest  $request
     * @return JsonResponse
     */
    public function deleteAccount(DeleteAccountRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user instanceof User) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $this->userService->deleteUser($user);

        // Logout the user after account deletion
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Account deleted successfully'], 200);
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
     * @return AnonymousResourceCollection
     */
    public function search(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'query' => 'required|string|min:2',
        ]);

        $users = $this->userService->searchUsers($validated['query']);
        return UserResource::collection($users);
    }

    /**
     * @return AnonymousResourceCollection
     */
    public function getTopUsers(): AnonymousResourceCollection
    {
        $users = $this->userService->getTopUsers();
        return UserResource::collection($users);
    }
}
