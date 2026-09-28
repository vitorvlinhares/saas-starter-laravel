<?php

namespace App\Tenancy;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Restricts tenant-owned models to the current team.
 *
 * Fail-closed: with no team in context the query returns nothing, so a missing
 * tenant can never leak another team's rows. Use TenantContext::bypass() for
 * legitimate cross-tenant work.
 */
class TeamScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $context = app(TenantContext::class);

        if ($context->isBypassing()) {
            return;
        }

        $teamId = $context->id();

        if ($teamId === null) {
            $builder->whereRaw('1 = 0');

            return;
        }

        $builder->where($model->qualifyColumn('team_id'), $teamId);
    }
}
