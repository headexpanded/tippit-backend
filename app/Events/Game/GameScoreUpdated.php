<?php

namespace App\Events\Game;

use App\Models\Game;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameScoreUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Game $game;
    public int $oldHomeScore;
    public int $oldAwayScore;

    public function __construct(Game $game, int $oldHomeScore, int $oldAwayScore)
    {
        $this->game = $game;
        $this->oldHomeScore = $oldHomeScore;
        $this->oldAwayScore = $oldAwayScore;
    }
}
