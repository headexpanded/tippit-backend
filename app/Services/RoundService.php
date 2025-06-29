<?php

namespace App\Services;

use App\Models\Round;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class RoundService extends BaseService
{
    /**
     * @param Round $model
     */
    public function __construct(Round $model)
    {
        parent::__construct($model);
    }

    /**
     * Get the next round (first round with is_completed = false)
     *
     * @return Round|null
     */
    public function getNextRound(): ?Round
    {
        return $this->model
            ->where('is_completed', false)
            ->orderBy('start_date')
            ->first();
    }

    /**
     * Get round with games and user predictions
     *
     * @param Round $round
     * @param User|null $user
     * @return Round
     */
    public function getRoundWithGamesAndPredictions(Round $round, ?User $user = null): Round
    {
        $query = $round->load(['games.homeTeam', 'games.awayTeam']);

        if ($user) {
            $query->load(['games.predictions' => function ($query) use ($user) {
                $query->where('user_id', $user->id);
            }]);
        }

        return $query;
    }

    /**
     * Calculate lockout time for a round (10 minutes before first game)
     *
     * @param Round $round
     * @return Carbon|null
     */
    public function calculateLockoutTime(Round $round): ?Carbon
    {
        $firstGame = $round->games()
            ->orderBy('game_time')
            ->first();

        if (!$firstGame || !$firstGame->game_time) {
            return null;
        }

        return $firstGame->game_time->subMinutes(10);
    }

    /**
     * Check if round is locked for predictions
     *
     * @param Round $round
     * @return bool
     */
    public function isRoundLocked(Round $round): bool
    {
        $lockoutTime = $this->calculateLockoutTime($round);

        if (!$lockoutTime) {
            return true; // If no lockout time can be calculated, consider it locked
        }

        return now()->isAfter($lockoutTime);
    }

    /**
     * Get all completed rounds
     *
     * @return Collection
     */
    public function getCompletedRounds(): Collection
    {
        return $this->model
            ->where('is_completed', true)
            ->orderBy('start_date', 'desc')
            ->get();
    }
}
