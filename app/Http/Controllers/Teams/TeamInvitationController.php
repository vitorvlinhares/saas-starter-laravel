<?php

namespace App\Http\Controllers\Teams;

use App\Actions\AcceptTeamInvitation;
use App\Enums\TeamRole;
use App\Http\Controllers\Controller;
use App\Mail\TeamInvitationMail;
use App\Models\Team;
use App\Models\TeamInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TeamInvitationController extends Controller
{
    public function store(Request $request, Team $team): RedirectResponse
    {
        Gate::authorize('manageMembers', $team);

        $request->merge(['email' => Str::lower((string) $request->input('email'))]);

        $validated = $request->validate([
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('team_invitations')->where('team_id', $team->id),
            ],
            'role' => ['required', Rule::in(TeamRole::assignableValues())],
        ], [
            'email.unique' => 'This e-mail already has a pending invitation.',
        ]);

        if ($team->users()->where('email', $validated['email'])->exists()) {
            throw ValidationException::withMessages([
                'email' => 'This user is already a member of the team.',
            ]);
        }

        $seats = $team->users()->count() + $team->invitations()->count();

        if (! $team->plan()->allows('members', $seats)) {
            throw ValidationException::withMessages([
                'email' => "The {$team->plan()->name} plan allows up to {$team->plan()->limit('members')} members. Upgrade to invite more people.",
            ]);
        }

        $token = Str::random(64);

        $invitation = $team->invitations()->create([
            'email' => $validated['email'],
            'role' => $validated['role'],
            'token' => hash('sha256', $token),
            'expires_at' => now()->addDays(TeamInvitation::VALID_FOR_DAYS),
        ]);

        Mail::to($invitation->email)->send(new TeamInvitationMail($invitation, $token));

        return back();
    }

    public function destroy(Team $team, TeamInvitation $invitation): RedirectResponse
    {
        Gate::authorize('manageMembers', $team);

        $invitation->delete();

        return back();
    }

    public function accept(Request $request, string $token, AcceptTeamInvitation $accept): RedirectResponse
    {
        $invitation = TeamInvitation::where('token', hash('sha256', $token))->firstOrFail();

        abort_if($invitation->isExpired(), 410, 'This invitation has expired. Ask the team for a new one.');

        abort_unless(
            Str::lower($request->user()->email) === Str::lower($invitation->email),
            403,
            'This invitation was sent to a different e-mail address.',
        );

        $accept->handle($invitation, $request->user());

        return to_route('dashboard');
    }
}
