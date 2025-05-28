<?php

namespace App\Events\MiniLeague;

use App\Models\MiniLeague;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MiniLeagueCreated
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public MiniLeague $miniLeague;

    /**
     * @param  MiniLeague  $miniLeague
     */
    public function __construct(MiniLeague $miniLeague)
    {
        $this->miniLeague = $miniLeague;
    }
}
