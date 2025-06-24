<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoundUserStatistics extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'round_id',
        'points_earned',
        'predictions_made',
        'correct_predictions',
        'exact_score_predictions',
        'total_points',
    ];

    protected $casts = [
        'points_earned' => 'integer',
        'predictions_made' => 'integer',
        'correct_predictions' => 'integer',
        'exact_score_predictions' => 'integer',
        'total_points' => 'integer',
    ];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo
     */
    public function round(): BelongsTo
    {
        return $this->belongsTo(Round::class);
    }


}
