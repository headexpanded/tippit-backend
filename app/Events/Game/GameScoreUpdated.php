<?php

namespace App\Events\Game;

use App\Models\Game;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameScoreUpdated
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public Game $game;
    public int $oldHomeScore;
    public int $oldAwayScore;

    /**
     * @param  Game  $game
     * @param  int  $oldHomeScore
     * @param  int  $oldAwayScore
     */
    public function __construct(Game $game, int $oldHomeScore, int $oldAwayScore)
    {
        $this->game = $game;
        $this->oldHomeScore = $oldHomeScore;
        $this->oldAwayScore = $oldAwayScore;
    }
}
