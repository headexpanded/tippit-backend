<?php

namespace App\Policies;

use App\Models\Game;
use App\Models\User;

class GamePolicy
{
    /**
     * @param  User|null  $user
     *
     * @return bool
     */
    public function viewAny(?User $user): bool
    {
        return true; // Anyone can view games
    }

    /**
     * @param  User|null  $user
     * @param  Game  $game
     *
     * @return bool
     */
    public function view(?User $user, Game $game): bool
    {
        return true; // Anyone can view a specific game
    }

    /**
     * @param  User  $user
     *
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * @param  User  $user
     * @param  Game  $game
     *
     * @return bool
     */
    public function update(User $user, Game $game): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * @param  User  $user
     * @param  Game  $game
     *
     * @return bool
     */
    public function delete(User $user, Game $game): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * @param  User  $user
     * @param  Game  $game
     *
     * @return bool
     */
    public function updateScore(User $user, Game $game): bool
    {
        return $user->hasRole('admin');
    }
}
