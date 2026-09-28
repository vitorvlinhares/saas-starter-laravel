<?php

namespace App\Http\Controllers\Teams;

use App\Actions\CreateTeam;
use App\Enums\TeamRole;
use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TeamController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('teams/index', [
            'teams' => $user->teams()
                ->withCount('users')
                ->orderBy('name')
                ->get()
                ->map(fn (Team $team) => [
                    'id' => $team->id,
                    'name' => $team->name,
                    'personal_team' => $team->personal_team,
                    'role' => TeamRole::from($team->pivot->role)->label(),
                    'members_count' => $team->users_count,
                    'is_current' => $team->id === $user->current_team_id,
                ]),
        ]);
    }

    public function store(Request $request, CreateTeam $createTeam): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $team = $createTeam->handle($request->user(), $validated['name']);

        return to_route('teams.show', $team);
    }

    public function show(Request $request, Team $team): Response
    {
        Gate::authorize('view', $team);

        $user = $request->user();

        return Inertia::render('teams/show', [
            'team' => [
                'id' => $team->id,
                'name' => $team->name,
                'personal_team' => $team->personal_team,
                'owner_id' => $team->user_id,
            ],
            'members' => $team->users()
                ->orderBy('name')
                ->get()
                ->map(fn (User $member) => [
                    'id' => $member->id,
                    'name' => $member->name,
                    'email' => $member->email,
                    'role' => $member->pivot->role,
                ]),
            'invitations' => Gate::allows('manageMembers', $team)
                ? $team->invitations()->latest()->get()->map(fn ($invitation) => [
                    'id' => $invitation->id,
                    'email' => $invitation->email,
                    'role' => $invitation->role->value,
                    'expired' => $invitation->isExpired(),
                ])
                : [],
            'roles' => collect(TeamRole::assignable())->map(fn (TeamRole $role) => [
                'value' => $role->value,
                'label' => $role->label(),
            ]),
            'permissions' => [
                'update' => Gate::allows('update', $team),
                'manageMembers' => Gate::allows('manageMembers', $team),
                'delete' => Gate::allows('delete', $team),
            ],
            'plan' => $team->plan()->toArray(),
            'currentUserId' => $user->id,
        ]);
    }

    public function update(Request $request, Team $team): RedirectResponse
    {
        Gate::authorize('update', $team);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $team->update($validated);

        return back();
    }

    public function destroy(Request $request, Team $team): RedirectResponse
    {
        Gate::authorize('delete', $team);

        $request->validate([
            'name' => ['required', 'string', Rule::in([$team->name])],
        ], [
            'name.in' => 'Type the team name exactly to confirm.',
        ]);

        if ($team->subscribed('default')) {
            $team->subscription('default')->cancelNow();
        }

        DB::transaction(function () use ($team) {
            User::where('current_team_id', $team->id)->update(['current_team_id' => null]);
            $team->delete();
        });

        $user = $request->user()->fresh();

        if ($personal = $user->personalTeam()) {
            $user->switchTeam($personal);
        }

        return to_route('teams.index');
    }
}
