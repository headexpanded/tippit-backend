<?php

namespace App\Listeners\League;

use App\Events\League\MemberJoined;
use App\Notifications\MemberJoinedLeague;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyMemberJoined implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * @param  MemberJoined  $event
     *
     * @return void
     */
    public function handle(MemberJoined $event): void
    {
        $league = $event->league;
        $newMember = $event->user;

        // Notify the league creator
        $creator = $league->creator;
        $creator->notify(new MemberJoinedLeague($league, $newMember));

        // Notify other members
        $otherMembers = $league->users()
            ->where('users.id', '!=', $newMember->id)
            ->where('users.id', '!=', $creator->id)
            ->get();

        foreach ($otherMembers as $member) {
            $member->notify(new MemberJoinedLeague($league, $newMember));
        }
    }
}
