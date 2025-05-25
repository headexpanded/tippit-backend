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
        'match_date',
        'match_time',
        'season',
        'home_score',
        'away_score',
        'status',
        'lockout_time',
    ];

    protected $casts = [
        'match_date' => 'date',
        'match_time' => 'datetime',
        'lockout_time' => 'datetime',
        'home_score' => 'integer',
        'away_score' => 'integer',
    ];

    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    public function predictions(): HasMany
    {
        return $this->hasMany(Prediction::class);
    }
}
