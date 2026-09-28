<?php

namespace Database\Seeders;

use App\Enums\TeamRole;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Demo data: log in with test@example.com / password.
     */
    public function run(): void
    {
        $owner = User::factory()->withPersonalTeam()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $team = $owner->currentTeam;

        $member = User::factory()->withPersonalTeam()->create([
            'name' => 'Team Member',
            'email' => 'member@example.com',
        ]);

        $team->users()->attach($member, ['role' => TeamRole::Member->value]);

        Project::factory()
            ->count(2)
            ->for($team)
            ->create(['created_by' => $owner->id]);
    }
}
