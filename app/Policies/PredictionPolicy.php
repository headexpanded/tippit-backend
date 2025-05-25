<?php

namespace App\Policies;

use App\Models\Prediction;
use App\Models\User;

class PredictionPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // Users can view their own predictions
    }

    public function view(User $user, Prediction $prediction): bool
    {
        return $user->id === $prediction->user_id || $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return true; // Any authenticated user can create predictions
    }

    public function update(User $user, Prediction $prediction): bool
    {
        return $user->id === $prediction->user_id && !$prediction->game->isLocked();
    }

    public function delete(User $user, Prediction $prediction): bool
    {
        return $user->id === $prediction->user_id && !$prediction->game->isLocked();
    }
}
