<?php

namespace App\Policies;

use App\Models\League;
use App\Models\User;

class LeaguePolicy
{
    /**
     * @param  User  $user
     *
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return true; // Users can view mini leagues they're part of
    }

    /**
     * @param  User  $user
     * @param  League  $league
     *
     * @return bool
     */
    public function view(User $user, League $league): bool
    {
        return $league->users()->where('users.id', $user->id)->exists() || $user->hasRole('admin');
    }

    /**
     * @param  User  $user
     *
     * @return bool
     */
    public function create(User $user): bool
    {
        return true; // Any authenticated user can create a mini league
    }

    /**
     * @param  User  $user
     * @param  League  $league
     *
     * @return bool
     */
    public function update(User $user, League $league): bool
    {
        return $user->id === $league->created_by;
    }

    /**
     * @param  User  $user
     * @param  League  $league
     *
     * @return bool
     */
    public function delete(User $user, League $league): bool
    {
        return $user->id === $league->created_by;
    }

    /**
     * @param  User  $user
     * @param  League  $league
     *
     * @return bool
     */
    public function addMember(User $user, League $league): bool
    {
        return $user->id === $league->created_by;
    }

    /**
     * @param  User  $user
     * @param  League  $league
     *
     * @return bool
     */
    public function removeMember(User $user, League $league): bool
    {
        return $user->id === $league->created_by;
    }

    /**
     * @param  User  $user
     * @param  League  $league
     *
     * @return bool
     */
    public function leave(User $user, League $league): bool
    {
        return $league->users()->where('users.id', $user->id)->exists() &&
               $user->id !== $league->created_by;
    }
}
