<?php

namespace App\Services;

use App\Events\MiniLeague\MemberJoined;
use App\Events\MiniLeague\MiniLeagueCreated;
use App\Models\MiniLeague;
use App\Models\User;
use Exception;
use Illuminate\Support\Collection;

class MiniLeagueService extends BaseService
{
    /**
     * @param  Model  $model
     */
    public function __construct(MiniLeague $model)
    {
        parent::__construct($model);
    }

    /**
     * @param  User  $creator
     * @param  array  $data
     *
     * @return MiniLeague
     */
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

    /**
     * @param  MiniLeague  $miniLeague
     * @param  array  $data
     *
     * @return MiniLeague
     * @throws Exception
     */
    public function updateMiniLeague(MiniLeague $miniLeague, array $data): MiniLeague
    {
        if ($miniLeague->created_by !== auth()->id()) {
            throw new Exception('Only the creator can update the mini league.');
        }

        $miniLeague->update($data);
        return $miniLeague;
    }

    /**
     * @param  MiniLeague  $miniLeague
     *
     * @return bool
     * @throws Exception
     */
    public function deleteMiniLeague(MiniLeague $miniLeague): bool
    {
        if ($miniLeague->created_by !== auth()->id()) {
            throw new Exception('Only the creator can delete the mini league.');
        }

        return $miniLeague->delete();
    }

    /**
     * @param  User  $user
     *
     * @return Collection
     */
    public function getUserMiniLeagues(User $user): Collection
    {
        return $this->model->with(['creator', 'users'])
            ->whereHas('users', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->get();
    }

    /**
     * @param  MiniLeague  $miniLeague
     * @param  User  $user
     *
     * @return void
     * @throws Exception
     */
    public function addMember(MiniLeague $miniLeague, User $user): void
    {
        if ($miniLeague->created_by !== auth()->id()) {
            throw new Exception('Only the creator can add members.');
        }

        // Check if the league is full
        if ($miniLeague->users()->count() >= 10) {
            throw new Exception('Mini league is full.');
        }

        // Check if the user is already a member
        if ($miniLeague->users()->where('users.id', $user->id)->exists()) {
            throw new Exception('User is already a member.');
        }

        $miniLeague->users()->attach($user->id, ['joined_at' => now()]);

        event(new MemberJoined($miniLeague, $user));
    }

    /**
     * @param  MiniLeague  $miniLeague
     * @param  User  $user
     *
     * @return void
     * @throws Exception
     */
    public function removeMember(MiniLeague $miniLeague, User $user): void
    {
        if ($miniLeague->created_by !== auth()->id()) {
            throw new Exception('Only the creator can remove members.');
        }

        // Cannot remove the creator
        if ($user->id === $miniLeague->created_by) {
            throw new Exception('Cannot remove the league creator.');
        }

        $miniLeague->users()->detach($user->id);
    }

    /**
     * @param  MiniLeague  $miniLeague
     * @param  User  $user
     *
     * @return void
     * @throws Exception
     */
    public function leaveMiniLeague(MiniLeague $miniLeague, User $user): void
    {
        // Cannot leave if you're the creator
        if ($user->id === $miniLeague->created_by) {
            throw new Exception('League creator cannot leave. Transfer ownership or delete the league.');
        }

        $miniLeague->users()->detach($user->id);
    }

    /**
     * @param  MiniLeague  $miniLeague
     *
     * @return Collection
     */
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
