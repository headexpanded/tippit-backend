<?php

namespace App\Events\League;

use App\Models\League;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MemberJoined
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public League $league;
    public User $user;

    /**
     * @param  League  $league
     * @param  User  $user
     */
    public function __construct(League $league, User $user)
    {
        $this->league = $league;
        $this->user = $user;
    }
}
