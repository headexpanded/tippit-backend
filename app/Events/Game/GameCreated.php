<?php

namespace App\Events\Game;

use App\Models\Game;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Game $game;

    public function __construct(Game $game)
    {
        $this->game = $game;
    }
}
