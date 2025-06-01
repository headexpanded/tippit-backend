<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteStatistics extends Model
{
    protected $fillable = [
        'total_users',
        'active_users',
        'total_predictions',
        'total_matches',
        'total_leagues',
    ];

    protected $casts = [
        'total_users' => 'integer',
        'active_users' => 'integer',
        'total_predictions' => 'integer',
        'total_games' => 'integer',
        'total_leagues' => 'integer',
    ];
}
