<?php

namespace Database\Factories;

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\TeamInvitation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TeamInvitation>
 */
class TeamInvitationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'email' => fake()->unique()->safeEmail(),
            'role' => TeamRole::Member,
            'token' => hash('sha256', Str::random(64)),
            'expires_at' => now()->addDays(TeamInvitation::VALID_FOR_DAYS),
        ];
    }

    /**
     * Use a known plain token (the stored value is its hash).
     */
    public function withToken(string $token): static
    {
        return $this->state(fn () => ['token' => hash('sha256', $token)]);
    }

    public function expired(): static
    {
        return $this->state(fn () => ['expires_at' => now()->subDay()]);
    }
}
