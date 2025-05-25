<?php

namespace App\Listeners\Game;

use App\Events\Game\GameCreated;
use App\Notifications\GameCreated as GameCreatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyGameCreated implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(GameCreated $event): void
    {
        // Notify all users about the new game
        $users = \App\Models\User::whereHas('preferences', function ($query) {
            $query->where('notify_new_games', true);
        })->get();

        foreach ($users as $user) {
            $user->notify(new GameCreatedNotification($event->game));
        }
    }
}
