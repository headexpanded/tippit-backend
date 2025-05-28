<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class UserService extends BaseService
{
    /**
     * @param  Model  $model
     */
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * @param  array  $data
     *
     * @return User
     */
    public function createUser(array $data): User
    {
        $data['password'] = Hash::make($data['password']);

        return $this->model->create($data);
    }

    /**
     * @param  User  $user
     * @param  array  $data
     *
     * @return User
     */
    public function updateUser(User $user, array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);
        return $user;
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
     * @param  string  $email
     *
     * @return string
     */
    public function sendPasswordResetLink(string $email): string
    {
        return Password::sendResetLink(['email' => $email]);
    }

    /**
     * @param  string  $email
     * @param  string  $password
     * @param  string  $token
     *
     * @return bool
     */
    public function resetPassword(string $email, string $password, string $token): bool
    {
        $status = Password::reset(
            [
                'email' => $email,
                'password' => $password,
                'password_confirmation' => $password,
                'token' => $token,
            ],
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET;
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
