<?php

namespace App\Providers;

use App\Events\Game\GameCreated;
use App\Events\Game\GameScoreUpdated;
use App\Events\MiniLeague\MemberJoined;
use App\Events\MiniLeague\MiniLeagueCreated;
use App\Events\Prediction\PredictionCreated;
use App\Events\Prediction\PredictionUpdated;
use App\Listeners\Game\NotifyGameCreated;
use App\Listeners\Game\ProcessGameScoreUpdate;
use App\Listeners\MiniLeague\NotifyMemberJoined;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        GameCreated::class => [
            NotifyGameCreated::class,
        ],
        GameScoreUpdated::class => [
            ProcessGameScoreUpdate::class,
        ],
        PredictionCreated::class => [
            // Add prediction created listeners here
        ],
        PredictionUpdated::class => [
            // Add prediction updated listeners here
        ],
        MiniLeagueCreated::class => [
            // Add mini league created listeners here
        ],
        MemberJoined::class => [
            NotifyMemberJoined::class,
        ],
    ];

    /**
     * Boot any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
