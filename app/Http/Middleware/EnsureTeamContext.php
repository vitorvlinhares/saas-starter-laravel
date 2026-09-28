<?php

namespace App\Http\Middleware;

use App\Actions\CreateTeam;
use App\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the current team for authenticated requests and stores it in the
 * TenantContext. Repairs a stale current_team_id (e.g. after being removed
 * from a team) and creates a personal team for users that have none.
 */
class EnsureTeamContext
{
    public function __construct(
        private readonly TenantContext $context,
        private readonly CreateTeam $createTeam,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        $team = $user->currentTeam;

        if ($team === null || ! $user->belongsToTeam($team)) {
            $team = $user->teams()->orderBy('teams.id')->first();

            if ($team !== null) {
                $user->switchTeam($team);
            } else {
                $team = $this->createTeam->personal($user);
            }
        }

        $this->context->set($team);

        return $next($request);
    }
}
