<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class League extends Model
{
    protected $fillable = [
        'name',
        'description',
        'created_by',
    ];

    /**
     * @return BelongsTo
     */
    public function creator() : BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
                    ->withTimestamps()
                    ->withPivot('joined_at');
    }

    /**
     * @return HasMany
     */
    public function messages(): HasMany
    {
        return $this->hasMany(LeagueMessage::class);
    }

    /**
     * @return HasOne
     */
    public function ranking(): HasOne
    {
        return $this->hasOne(LeagueRanking::class);
    }
}
