<?php

namespace App\Listeners\MiniLeague;

use App\Events\MiniLeague\MemberJoined;
use App\Notifications\MemberJoinedMiniLeague;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyMemberJoined implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(MemberJoined $event): void
    {
        $miniLeague = $event->miniLeague;
        $newMember = $event->user;

        // Notify the league creator
        $creator = $miniLeague->creator;
        $creator->notify(new MemberJoinedMiniLeague($miniLeague, $newMember));

        // Notify other members
        $otherMembers = $miniLeague->users()
            ->where('users.id', '!=', $newMember->id)
            ->where('users.id', '!=', $creator->id)
            ->get();

        foreach ($otherMembers as $member) {
            $member->notify(new MemberJoinedMiniLeague($miniLeague, $newMember));
        }
    }
}
