<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserStatistics extends Model
{
    protected $fillable = [
        'user_id',
        'total_points',
        'latest_points',
        'rounds_played',
        'total_predictions',
        'correct_predictions',
        'exact_score_predictions',
        'current_rank',
        'best_rank',
    ];

    protected $casts = [
        'total_points' => 'integer',
        'latest_points' => 'integer',
        'rounds_played' => 'integer',
        'total_predictions' => 'integer',
        'correct_predictions' => 'integer',
        'exact_score_predictions' => 'integer',
        'current_rank' => 'integer',
        'best_rank' => 'integer',
    ];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
