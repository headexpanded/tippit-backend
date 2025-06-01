<?php

namespace App\Providers;

use App\Models\Game;
use App\Models\League;
use App\Models\Prediction;
use App\Policies\GamePolicy;
use App\Policies\LeaguePolicy;
use App\Policies\PredictionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Game::class => GamePolicy::class,
        Prediction::class => PredictionPolicy::class,
        League::class => LeaguePolicy::class,
    ];

    /**
     * @return void
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
