<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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
     * @param  Request  $request
     *
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $this->userService->createUser($request->all());
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ], 201);
    }

    /**
     * @param  Request  $request
     *
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $user = $this->userService->getUserByEmail($request->email);
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        $user = Auth::user();
        $user->tokens()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }

    /**
     * @return JsonResponse
     */
    public function profile(): JsonResponse
    {
        $user = Auth::user();
        $user->load(['statistics', 'predictions', 'miniLeagues']);
        return response()->json($user);
    }

    /**
     * @param  Request  $request
     *
     * @return JsonResponse
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'email' => 'string|email|max:255|unique:users,email,' . $user->id,
            'current_password' => 'required_with:new_password|string',
            'new_password' => 'string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Verify the current password if changing password
        if ($request->has('new_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json(['error' => 'Current password is incorrect'], 422);
            }
            $user->password = Hash::make($request->new_password);
        }

        $user->name = $request->name ?? $user->name;
        $user->email = $request->email ?? $user->email;
        $user->save();

        return response()->json($user);
    }

    /**
     * @return JsonResponse
     */
    public function getPredictions(): JsonResponse
    {
        $user = Auth::user();
        $predictions = $user->predictions()
            ->with(['game.homeTeam', 'game.awayTeam'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($predictions);
    }

    /**
     * @return JsonResponse
     */
    public function getMiniLeagues(): JsonResponse
    {
        $user = Auth::user();
        $miniLeagues = $user->miniLeagues()
            ->with(['creator', 'users'])
            ->get();

        return response()->json($miniLeagues);
    }

    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $users = $this->userService->getActiveUsers();
        return response()->json($users);
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
     * @param  Request  $request
     * @param  User  $user
     *
     * @return JsonResponse
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'email' => 'string|email|max:255|unique:users,email,' . $user->id,
            'username' => 'string|max:255|unique:users,username,' . $user->id,
            'password' => 'nullable|string|min:8',
        ]);

        $user = $this->userService->updateUser($user, $validated);
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

        $user = $this->userService->updateUserPreferences(Auth::user(), $validated['preferences']);
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

    /**
     * @param  Request  $request
     *
     * @return JsonResponse
     */
    public function sendPasswordResetLink(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $status = $this->userService->sendPasswordResetLink($validated['email']);
        return response()->json(['message' => 'Password reset link sent']);
    }

    /**
     * @param  Request  $request
     *
     * @return JsonResponse
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
            'token' => 'required|string',
        ]);

        $success = $this->userService->resetPassword(
            $validated['email'],
            $validated['password'],
            $validated['token']
        );

        if ($success) {
            return response()->json(['message' => 'Password reset successfully']);
        }

        return response()->json(['message' => 'Unable to reset password'], 400);
    }
}
