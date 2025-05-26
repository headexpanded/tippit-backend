<?php

namespace App\Providers;

use App\Models\Game;
use App\Models\MiniLeague;
use App\Models\Prediction;
use App\Policies\GamePolicy;
use App\Policies\MiniLeaguePolicy;
use App\Policies\PredictionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Game::class => GamePolicy::class,
        Prediction::class => PredictionPolicy::class,
        MiniLeague::class => MiniLeaguePolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
