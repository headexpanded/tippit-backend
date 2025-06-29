<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    protected $fillable = [
        'home_team_id',
        'away_team_id',
        'game_date',
        'game_time',
        'season',
        'home_score',
        'away_score',
        'status',
        'lockout_time',
    ];

    protected $casts = [
        'game_date' => 'date',
        'game_time' => 'datetime',
        'lockout_time' => 'datetime',
        'home_score' => 'integer',
        'away_score' => 'integer',
    ];

    /**
     * @return BelongsTo
     */
    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    /**
     * @return BelongsTo
     */
    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    /**
     * @return HasMany
     */
    public function predictions(): HasMany
    {
        return $this->hasMany(Prediction::class);
    }

    /**
     * @return BelongsTo
     */
    public function round(): BelongsTo
    {
        return $this->belongsTo(Round::class);
    }

    /**
     * Check if the game is locked for predictions
     *
     * @return bool
     */
    public function isLocked(): bool
    {
        // If the game has a specific lockout time, use that
        if ($this->lockout_time) {
            return now()->isAfter($this->lockout_time);
        }

        // Otherwise, use the round's lockout time (10 minutes before first game)
        if ($this->round) {
            $firstGame = $this->round->games()
                ->orderBy('game_time')
                ->first();

            if ($firstGame && $firstGame->game_time) {
                $lockoutTime = $firstGame->game_time->subMinutes(10);
                return now()->isAfter($lockoutTime);
            }
        }

        return true; // Default to locked if we can't determine
    }

}
