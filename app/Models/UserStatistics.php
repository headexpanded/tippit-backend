<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserStatistics extends Model
{
    protected $fillable = [
        'user_id',
        'total_points',
        'total_predictions',
        'correct_predictions',
        'exact_score_predictions',
        'current_rank',
        'best_rank',
    ];

    protected $casts = [
        'total_points' => 'integer',
        'total_predictions' => 'integer',
        'correct_predictions' => 'integer',
        'exact_score_predictions' => 'integer',
        'current_rank' => 'integer',
        'best_rank' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
