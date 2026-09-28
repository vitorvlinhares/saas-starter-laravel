<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class BillingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'plans.plans.pro.price_id' => 'price_test_pro',
            'plans.plans.business.price_id' => 'price_test_business',
        ]);
    }

    public function test_teams_start_on_the_free_plan(): void
    {
        $team = Team::factory()->create();

        $this->assertSame('free', $team->plan()->key);
    }

    public function test_an_active_subscription_resolves_its_plan(): void
    {
        $team = $this->subscribe(Team::factory()->create(), 'price_test_pro');

        $this->assertSame('pro', $team->plan()->key);
        $this->assertSame(50, $team->plan()->limit('projects'));
    }

    public function test_a_cancelled_subscription_falls_back_to_free(): void
    {
        $team = $this->subscribe(Team::factory()->create(), 'price_test_pro', status: 'canceled', endsAt: now()->subDay());

        $this->assertSame('free', $team->plan()->key);
    }

    public function test_the_free_plan_project_limit_is_enforced(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        Project::factory()->count(3)->for($user->currentTeam)->create();

        $this->actingAs($user)
            ->post('/projects', ['name' => 'One too many'])
            ->assertSessionHasErrors('name');

        $this->assertDatabaseMissing('projects', ['name' => 'One too many']);
    }

    public function test_paid_plans_raise_the_limit(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $this->subscribe($user->currentTeam, 'price_test_pro');
        Project::factory()->count(3)->for($user->currentTeam)->create();

        $this->actingAs($user)
            ->post('/projects', ['name' => 'Fourth'])
            ->assertSessionHasNoErrors();
    }

    public function test_the_billing_page_shows_plans_and_usage(): void
    {
        $user = User::factory()->withPersonalTeam()->create();

        $this->actingAs($user)
            ->get('/billing')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('billing/index')
                ->where('currentPlan', 'free')
                ->where('canManage', true)
                ->where('usage.members', 1)
                ->has('plans', 3));
    }

    public function test_only_the_owner_can_start_a_checkout(): void
    {
        $team = Team::factory()->create();
        $member = User::factory()->create();
        $team->users()->attach($member, ['role' => 'admin']);
        $member->switchTeam($team);

        $this->actingAs($member)
            ->post('/billing/checkout', ['plan' => 'pro'])
            ->assertForbidden();
    }

    public function test_the_free_plan_cannot_be_checked_out(): void
    {
        $user = User::factory()->withPersonalTeam()->create();

        $this->actingAs($user)
            ->post('/billing/checkout', ['plan' => 'free'])
            ->assertSessionHasErrors('plan');
    }

    private function subscribe(Team $team, string $price, string $status = 'active', $endsAt = null): Team
    {
        $team->subscriptions()->create([
            'type' => 'default',
            'stripe_id' => 'sub_'.uniqid(),
            'stripe_status' => $status,
            'stripe_price' => $price,
            'quantity' => 1,
            'ends_at' => $endsAt,
        ]);

        return $team->refresh();
    }
}
