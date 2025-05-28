<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;

class UserService extends BaseService
{
    /**
     * @param  User  $model
     */
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * @param  User  $user
     *
     * @return bool
     */
    public function deleteUser(User $user): bool
    {
        return $user->delete();
    }

    /**
     * @param  string  $email
     *
     * @return User|null
     */
    public function getUserByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * @param  string  $username
     *
     * @return User|null
     */
    public function getUserByUsername(string $username): ?User
    {
        return $this->model->where('username', $username)->first();
    }

    /**
     * @return Collection
     */
    public function getActiveUsers(): Collection
    {
        return $this->model
            ->where('last_login_at', '>=', now()->subDays(30))
            ->get();
    }

    /**
     * @param  User  $user
     *
     * @return void
     */
    public function updateLastLogin(User $user): void
    {
        $user->update(['last_login_at' => now()]);
    }

    /**
     * @param  User  $user
     * @param  array  $preferences
     *
     * @return User
     */
    public function updateUserPreferences(User $user, array $preferences): User
    {
        $user->update(['preferences' => array_merge($user->preferences ?? [], $preferences)]);
        return $user;
    }

    /**
     * @param  User  $user
     *
     * @return User
     */
    public function getUserWithRelations(User $user): User
    {
        return $user->load([
            'statistics',
            'predictions.game',
            'miniLeagues',
            'ownedMiniLeagues',
        ]);
    }

    /**
     * @param  string  $query
     *
     * @return Collection
     */
    public function searchUsers(string $query): Collection
    {
        return $this->model
            ->where('username', 'like', "%{$query}%")
            ->orWhere('name', 'like', "%{$query}%")
            ->get();
    }

    /**
     * @param  int  $limit
     *
     * @return Collection
     */
    public function getTopUsers(int $limit = 10): Collection
    {
        return $this->model
            ->with('statistics')
            ->whereHas('statistics')
            ->get()
            ->sortByDesc(function ($user) {
                return $user->statistics->total_points;
            })
            ->take($limit)
            ->values();
    }
}
