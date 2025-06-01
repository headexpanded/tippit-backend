<?php

namespace App\Events\League;

use App\Models\League;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LeagueCreated
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public League $league;

    /**
     * @param  League  $league
     */
    public function __construct(League $league)
    {
        $this->league = $league;
    }
}
