<?php

namespace App\Listeners\Game;

use App\Events\Game\GameScoreUpdated;
use App\Events\League\RankingsUpdated;
use App\Notifications\GameScoreUpdated as GameScoreUpdatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ProcessGameScoreUpdate implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * @param  GameScoreUpdated  $event
     *
     * @return void
     */
    public function handle(GameScoreUpdated $event): void
    {
        $game = $event->game;
        $predictions = $game->predictions;

        foreach ($predictions as $prediction) {
            $user = $prediction->user;

            // Notify user about their prediction result
            $user->notify(new GameScoreUpdatedNotification(
                $game,
                $prediction,
                $event->oldHomeScore,
                $event->oldAwayScore
            ));

            // Update mini league rankings if the user is in any mini leagues
            $miniLeagues = $user->miniLeagues;
            foreach ($miniLeagues as $miniLeague) {
                event(new RankingsUpdated($miniLeague));
            }
        }
    }
}
