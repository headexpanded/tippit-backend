<?php

namespace App\Policies;

use App\Models\Game;
use App\Models\User;

class GamePolicy
{
    public function viewAny(?User $user): bool
    {
        return true; // Anyone can view games
    }

    public function view(?User $user, Game $game): bool
    {
        return true; // Anyone can view a specific game
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, Game $game): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, Game $game): bool
    {
        return $user->hasRole('admin');
    }

    public function updateScore(User $user, Game $game): bool
    {
        return $user->hasRole('admin');
    }
}
