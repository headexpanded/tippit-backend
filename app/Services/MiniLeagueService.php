<?php

namespace App\Services;

use App\Events\MiniLeague\MemberJoined;
use App\Events\MiniLeague\MiniLeagueCreated;
use App\Models\MiniLeague;
use App\Models\User;
use Illuminate\Support\Collection;

class MiniLeagueService extends BaseService
{
    public function __construct(MiniLeague $model)
    {
        parent::__construct($model);
    }

    public function createMiniLeague(User $creator, array $data): MiniLeague
    {
        $miniLeague = $this->model->create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'created_by' => $creator->id,
        ]);

        // Add creator as first member
        $miniLeague->users()->attach($creator->id, ['joined_at' => now()]);

        event(new MiniLeagueCreated($miniLeague));

        return $miniLeague;
    }

    public function updateMiniLeague(MiniLeague $miniLeague, array $data): MiniLeague
    {
        if ($miniLeague->created_by !== auth()->id()) {
            throw new \Exception('Only the creator can update the mini league.');
        }

        $miniLeague->update($data);
        return $miniLeague;
    }

    public function deleteMiniLeague(MiniLeague $miniLeague): bool
    {
        if ($miniLeague->created_by !== auth()->id()) {
            throw new \Exception('Only the creator can delete the mini league.');
        }

        return $miniLeague->delete();
    }

    public function getUserMiniLeagues(User $user): Collection
    {
        return $this->model->with(['creator', 'users'])
            ->whereHas('users', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->get();
    }

    public function addMember(MiniLeague $miniLeague, User $user): void
    {
        if ($miniLeague->created_by !== auth()->id()) {
            throw new \Exception('Only the creator can add members.');
        }

        // Check if league is full
        if ($miniLeague->users()->count() >= 10) {
            throw new \Exception('Mini league is full.');
        }

        // Check if user is already a member
        if ($miniLeague->users()->where('users.id', $user->id)->exists()) {
            throw new \Exception('User is already a member.');
        }

        $miniLeague->users()->attach($user->id, ['joined_at' => now()]);

        event(new MemberJoined($miniLeague, $user));
    }

    public function removeMember(MiniLeague $miniLeague, User $user): void
    {
        if ($miniLeague->created_by !== auth()->id()) {
            throw new \Exception('Only the creator can remove members.');
        }

        // Cannot remove the creator
        if ($user->id === $miniLeague->created_by) {
            throw new \Exception('Cannot remove the league creator.');
        }

        $miniLeague->users()->detach($user->id);
    }

    public function leaveMiniLeague(MiniLeague $miniLeague, User $user): void
    {
        // Cannot leave if you're the creator
        if ($user->id === $miniLeague->created_by) {
            throw new \Exception('League creator cannot leave. Transfer ownership or delete the league.');
        }

        $miniLeague->users()->detach($user->id);
    }

    public function getRankings(MiniLeague $miniLeague): Collection
    {
        return $miniLeague->users()
            ->with('statistics')
            ->get()
            ->map(function ($user) {
                return [
                    'user' => $user,
                    'points' => $user->statistics->total_points,
                    'rank' => $user->statistics->current_rank,
                ];
            })
            ->sortByDesc('points')
            ->values();
    }
}
