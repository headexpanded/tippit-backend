<?php

namespace App\Events\Game;

use App\Models\Game;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameCreated
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public Game $game;

    /**
     * @param  Game  $game
     */
    public function __construct(Game $game)
    {
        $this->game = $game;
    }
}
