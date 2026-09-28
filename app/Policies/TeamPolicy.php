<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;

class TeamPolicy
{
    public function view(User $user, Team $team): bool
    {
        return $user->belongsToTeam($team);
    }

    public function update(User $user, Team $team): bool
    {
        return (bool) $team->roleOf($user)?->canManageTeam();
    }

    public function manageMembers(User $user, Team $team): bool
    {
        return (bool) $team->roleOf($user)?->canManageTeam();
    }

    public function manageBilling(User $user, Team $team): bool
    {
        return $team->isOwnedBy($user);
    }

    public function delete(User $user, Team $team): bool
    {
        return $team->isOwnedBy($user) && ! $team->personal_team;
    }
}
