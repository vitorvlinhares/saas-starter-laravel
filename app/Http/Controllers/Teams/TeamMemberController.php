<?php

namespace App\Http\Controllers\Teams;

use App\Enums\TeamRole;
use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class TeamMemberController extends Controller
{
    public function update(Request $request, Team $team, User $user): RedirectResponse
    {
        Gate::authorize('manageMembers', $team);

        abort_unless($user->belongsToTeam($team), 404);
        abort_if($team->isOwnedBy($user), 403, "The owner's role cannot be changed.");

        $validated = $request->validate([
            'role' => ['required', Rule::in(TeamRole::assignableValues())],
        ]);

        $team->users()->updateExistingPivot($user->id, ['role' => $validated['role']]);

        return back();
    }

    public function destroy(Request $request, Team $team, User $user): RedirectResponse
    {
        $leaving = $request->user()->is($user);

        if (! $leaving) {
            Gate::authorize('manageMembers', $team);
        }

        abort_unless($user->belongsToTeam($team), 404);
        abort_if($team->isOwnedBy($user), 403, 'The owner cannot be removed from the team.');

        $team->users()->detach($user->id);

        if ($user->current_team_id === $team->id) {
            $user->forceFill(['current_team_id' => null])->save();
        }

        return $leaving ? to_route('dashboard') : back();
    }
}
