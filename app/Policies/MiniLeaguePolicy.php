<?php

namespace App\Policies;

use App\Models\MiniLeague;
use App\Models\User;

class MiniLeaguePolicy
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
     * @param  MiniLeague  $miniLeague
     *
     * @return bool
     */
    public function view(User $user, MiniLeague $miniLeague): bool
    {
        return $miniLeague->users()->where('users.id', $user->id)->exists() || $user->hasRole('admin');
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
     * @param  MiniLeague  $miniLeague
     *
     * @return bool
     */
    public function update(User $user, MiniLeague $miniLeague): bool
    {
        return $user->id === $miniLeague->created_by;
    }

    /**
     * @param  User  $user
     * @param  MiniLeague  $miniLeague
     *
     * @return bool
     */
    public function delete(User $user, MiniLeague $miniLeague): bool
    {
        return $user->id === $miniLeague->created_by;
    }

    /**
     * @param  User  $user
     * @param  MiniLeague  $miniLeague
     *
     * @return bool
     */
    public function addMember(User $user, MiniLeague $miniLeague): bool
    {
        return $user->id === $miniLeague->created_by;
    }

    /**
     * @param  User  $user
     * @param  MiniLeague  $miniLeague
     *
     * @return bool
     */
    public function removeMember(User $user, MiniLeague $miniLeague): bool
    {
        return $user->id === $miniLeague->created_by;
    }

    /**
     * @param  User  $user
     * @param  MiniLeague  $miniLeague
     *
     * @return bool
     */
    public function leave(User $user, MiniLeague $miniLeague): bool
    {
        return $miniLeague->users()->where('users.id', $user->id)->exists() &&
               $user->id !== $miniLeague->created_by;
    }
}
