<?php

namespace App\Events\MiniLeague;

use App\Models\MiniLeague;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MiniLeagueCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public MiniLeague $miniLeague;

    public function __construct(MiniLeague $miniLeague)
    {
        $this->miniLeague = $miniLeague;
    }
}
