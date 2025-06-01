<?php

namespace App\Services;

use App\Events\League\MemberJoined;
use App\Models\League;
use App\Models\User;
use Exception;
use Illuminate\Support\Collection;

class LeagueService extends BaseService
{
    /**
     * @param  League  $league
     */
    public function __construct(League $league)
    {
        parent::__construct($league);
    }

    /**
     * @param  User  $creator
     * @param  array  $data
     *
     * @return League
     */
    public function createLeague(User $creator, array $data): League
    {
        $league = $this->model->create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'created_by' => $creator->id,
        ]);

        // Add creator as first member
        $league->users()->attach($creator->id, ['joined_at' => now()]);

        event(new LeagueCreated($league));

        return $league;
    }

    /**
     * @param  League  $League
     * @param  array  $data
     *
     * @return League
     * @throws Exception
     */
    public function updateLeague(League $league, array $data): League
    {
        if ($league->created_by !== auth()->id()) {
            throw new Exception('Only the creator can update the mini league.');
        }

        $league->update($data);
        return $league;
    }

    /**
     * @param  League  $league
     *
     * @return bool
     * @throws Exception
     */
    public function deleteLeague(League $league): bool
    {
        if ($league->created_by !== auth()->id()) {
            throw new Exception('Only the creator can delete the league.');
        }

        return $league->delete();
    }

    /**
     * @param  User  $user
     *
     * @return Collection
     */
    public function getUserLeagues(User $user): Collection
    {
        return $this->model->with(['creator', 'users'])
            ->whereHas('users', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->get();
    }

    /**
     * @param  League  $league
     * @param  User  $user
     *
     * @return void
     * @throws Exception
     */
    public function addMember(League $league, User $user): void
    {
        if ($league->created_by !== auth()->id()) {
            throw new Exception('Only the creator can add members.');
        }

        // Check if the league is full
        if ($league->users()->count() >= 10) {
            throw new Exception('Mini league is full.');
        }

        // Check if the user is already a member
        if ($league->users()->where('users.id', $user->id)->exists()) {
            throw new Exception('User is already a member.');
        }

        $league->users()->attach($user->id, ['joined_at' => now()]);

        event(new MemberJoined($league, $user));
    }

    /**
     * @param  League  $league
     * @param  User  $user
     *
     * @return void
     * @throws Exception
     */
    public function removeMember(League $league, User $user): void
    {
        if ($league->created_by !== auth()->id()) {
            throw new Exception('Only the creator can remove members.');
        }

        // Cannot remove the creator
        if ($user->id === $league->created_by) {
            throw new Exception('Cannot remove the league creator.');
        }

        $league->users()->detach($user->id);
    }

    /**
     * @param  League  $league
     * @param  User  $user
     *
     * @return void
     * @throws Exception
     */
    public function leaveLeague(League $league, User $user): void
    {
        // Cannot leave if you're the creator
        if ($user->id === $league->created_by) {
            throw new Exception('League creator cannot leave. Transfer ownership or delete the league.');
        }

        $league->users()->detach($user->id);
    }

    /**
     * @param  League  $league
     *
     * @return Collection
     */
    public function getRankings(League $league): Collection
    {
        return $league->users()
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
