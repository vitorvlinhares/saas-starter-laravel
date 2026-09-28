<?php

namespace App\Http\Middleware;

use App\Models\Team;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        return array_merge(parent::share($request), [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $request->user(),
            ],
            'currentTeam' => fn () => $this->currentTeam($request),
            'teams' => fn () => $request->user()?->teams()
                ->orderBy('name')
                ->get(['teams.id', 'teams.name', 'teams.personal_team'])
                ->map(fn (Team $team) => [
                    'id' => $team->id,
                    'name' => $team->name,
                    'personal_team' => $team->personal_team,
                ]) ?? [],
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function currentTeam(Request $request): ?array
    {
        $user = $request->user();
        $team = $user?->currentTeam;

        if ($team === null) {
            return null;
        }

        $role = $team->roleOf($user);

        return [
            'id' => $team->id,
            'name' => $team->name,
            'personal_team' => $team->personal_team,
            'role' => $role?->value,
            'can_manage' => (bool) $role?->canManageTeam(),
            'plan' => $team->plan()->name,
        ];
    }
}
