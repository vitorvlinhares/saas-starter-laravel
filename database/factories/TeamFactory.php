<?php

namespace Database\Factories;

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Team>
 */
class TeamFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->company(),
            'personal_team' => false,
        ];
    }

    public function personal(): static
    {
        return $this->state(fn () => ['personal_team' => true]);
    }

    /**
     * Attach the owner as a member with the "owner" role.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Team $team) {
            if (! $team->users()->whereKey($team->user_id)->exists()) {
                $team->users()->attach($team->user_id, ['role' => TeamRole::Owner->value]);
            }
        });
    }
}
