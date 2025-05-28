<?php

namespace App\Policies;

use App\Models\Prediction;
use App\Models\User;

class PredictionPolicy
{
    /**
     * @param  User  $user
     *
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return true; // Users can view their own predictions
    }

    /**
     * @param  User  $user
     * @param  Prediction  $prediction
     *
     * @return bool
     */
    public function view(User $user, Prediction $prediction): bool
    {
        return $user->id === $prediction->user_id || $user->hasRole('admin');
    }

    /**
     * @param  User  $user
     *
     * @return bool
     */
    public function create(User $user): bool
    {
        return true; // Any authenticated user can create predictions
    }

    /**
     * @param  User  $user
     * @param  Prediction  $prediction
     *
     * @return bool
     */
    public function update(User $user, Prediction $prediction): bool
    {
        return $user->id === $prediction->user_id && !$prediction->game->isLocked();
    }

    /**
     * @param  User  $user
     * @param  Prediction  $prediction
     *
     * @return bool
     */
    public function delete(User $user, Prediction $prediction): bool
    {
        return $user->id === $prediction->user_id && !$prediction->game->isLocked();
    }
}
