<?php

namespace App\Actions;

use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AcceptTeamInvitation
{
    public function handle(TeamInvitation $invitation, User $user): void
    {
        DB::transaction(function () use ($invitation, $user) {
            $team = $invitation->team;

            if (! $user->belongsToTeam($team)) {
                $team->users()->attach($user, ['role' => $invitation->role->value]);
            }

            $invitation->delete();

            $user->switchTeam($team);
        });
    }
}
