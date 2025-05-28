<?php

namespace App\Events\MiniLeague;

use App\Models\MiniLeague;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MemberJoined
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public MiniLeague $miniLeague;
    public User $user;

    /**
     * @param  MiniLeague  $miniLeague
     * @param  User  $user
     */
    public function __construct(MiniLeague $miniLeague, User $user)
    {
        $this->miniLeague = $miniLeague;
        $this->user = $user;
    }
}
