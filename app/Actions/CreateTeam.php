<?php

namespace App\Actions;

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateTeam
{
    public function handle(User $owner, string $name, bool $personal = false): Team
    {
        return DB::transaction(function () use ($owner, $name, $personal) {
            $team = $owner->ownedTeams()->create([
                'name' => $name,
                'personal_team' => $personal,
            ]);

            $team->users()->attach($owner, ['role' => TeamRole::Owner->value]);

            $owner->switchTeam($team);

            return $team;
        });
    }

    public function personal(User $owner): Team
    {
        $firstName = explode(' ', trim($owner->name))[0] ?: $owner->name;

        return $this->handle($owner, "{$firstName}'s Team", personal: true);
    }
}
