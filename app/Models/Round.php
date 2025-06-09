<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Round extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_completed'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_completed' => 'boolean'
    ];

    /**
     * @return HasMany
     */
    public function games(): HasMany
    {
        return $this->hasMany(Game::class);
    }

    /**
     * @return bool
     */
    public function isPast(): bool
    {
        return now()->isAfter($this->end_date);
    }

    /**
     * @return bool
     */
    public function isFuture(): bool
    {
        return now()->isBefore($this->start_date);
    }

    /**
     * @return bool
     */
    public function isCurrent(): bool
    {
        return now()->between($this->start_date, $this->end_date);
    }

    /**
     * @return HasMany
     */
    public function userStatistics(): HasMany
    {
        return $this->hasMany(RoundUserStatistics::class);
    }

}
