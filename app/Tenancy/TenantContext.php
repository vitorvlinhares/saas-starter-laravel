<?php

namespace App\Tenancy;

use App\Models\Team;
use Illuminate\Support\Facades\Auth;

/**
 * Holds the team (tenant) for the current request or job.
 *
 * Bound as a scoped singleton, so it is reset between requests and queued jobs.
 * When nothing was set explicitly, it falls back to the authenticated user's
 * current team, which keeps route-model binding safe regardless of middleware order.
 */
class TenantContext
{
    private ?Team $team = null;

    private bool $bypassing = false;

    public function set(?Team $team): void
    {
        $this->team = $team;
    }

    public function team(): ?Team
    {
        if ($this->team === null) {
            $user = Auth::user();

            if ($user !== null && $user->current_team_id !== null) {
                $this->team = $user->currentTeam;
            }
        }

        return $this->team;
    }

    public function id(): ?int
    {
        return $this->team()?->getKey();
    }

    public function isBypassing(): bool
    {
        return $this->bypassing;
    }

    /**
     * Run a callback with the team scope disabled (seeders, admin tasks, reports).
     *
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    public function bypass(callable $callback): mixed
    {
        $previous = $this->bypassing;
        $this->bypassing = true;

        try {
            return $callback();
        } finally {
            $this->bypassing = $previous;
        }
    }
}
