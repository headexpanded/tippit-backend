<?php

namespace App\Policies;

use App\Models\MiniLeague;
use App\Models\User;

class MiniLeaguePolicy
{
    public function viewAny(User $user): bool
    {
        return true; // Users can view mini leagues they're part of
    }

    public function view(User $user, MiniLeague $miniLeague): bool
    {
        return $miniLeague->users()->where('users.id', $user->id)->exists() || $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return true; // Any authenticated user can create a mini league
    }

    public function update(User $user, MiniLeague $miniLeague): bool
    {
        return $user->id === $miniLeague->created_by;
    }

    public function delete(User $user, MiniLeague $miniLeague): bool
    {
        return $user->id === $miniLeague->created_by;
    }

    public function addMember(User $user, MiniLeague $miniLeague): bool
    {
        return $user->id === $miniLeague->created_by;
    }

    public function removeMember(User $user, MiniLeague $miniLeague): bool
    {
        return $user->id === $miniLeague->created_by;
    }

    public function leave(User $user, MiniLeague $miniLeague): bool
    {
        return $miniLeague->users()->where('users.id', $user->id)->exists() &&
               $user->id !== $miniLeague->created_by;
    }
}
