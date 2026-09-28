<?php

namespace App\Tenancy;

use App\Models\Team;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Marks a model as tenant-owned: queries are scoped to the current team and
 * new records are stamped with its id.
 *
 * @mixin Model
 */
trait BelongsToTeam
{
    public static function bootBelongsToTeam(): void
    {
        static::addGlobalScope(new TeamScope);

        static::creating(function (Model $model) {
            if ($model->getAttribute('team_id') === null) {
                $model->setAttribute('team_id', app(TenantContext::class)->id());
            }
        });
    }

    /**
     * @return BelongsTo<Team, $this>
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
