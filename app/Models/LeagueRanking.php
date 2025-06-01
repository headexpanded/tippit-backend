<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeagueRanking extends Model
{
    protected $fillable = [
        'league_id',
        'total_points',
        'average_points',
        'member_count',
    ];

    protected $casts = [
        'total_points' => 'integer',
        'average_points' => 'decimal:2',
        'member_count' => 'integer',
    ];

    /**
     * @return BelongsTo
     */
    public function league(): BelongsTo
    {
        return $this->belongsTo(League::class);
    }
}
