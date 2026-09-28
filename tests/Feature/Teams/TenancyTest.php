<?php

namespace Tests\Feature\Teams;

use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TenancyTest extends TestCase
{
    use RefreshDatabase;

    public function test_projects_are_listed_only_for_the_current_team(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        Project::factory()->for($user->currentTeam)->create(['name' => 'Mine']);
        Project::factory()->create(['name' => 'Someone else']);

        $this->actingAs($user)
            ->get('/projects')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('projects/index')
                ->has('projects', 1)
                ->where('projects.0.name', 'Mine'));
    }

    public function test_new_projects_are_stamped_with_the_current_team(): void
    {
        $user = User::factory()->withPersonalTeam()->create();

        $this->actingAs($user)
            ->post('/projects', ['name' => 'Launch'])
            ->assertRedirect();

        $this->assertDatabaseHas('projects', [
            'name' => 'Launch',
            'team_id' => $user->current_team_id,
            'created_by' => $user->id,
        ]);
    }

    public function test_projects_of_another_team_cannot_be_deleted(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $foreign = Project::factory()->create();

        $this->actingAs($user)
            ->delete(route('projects.destroy', $foreign))
            ->assertNotFound();

        $this->assertModelExists($foreign);
    }

    public function test_switching_teams_switches_the_visible_data(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $other = Team::factory()->create();
        $other->users()->attach($user, ['role' => 'member']);
        Project::factory()->for($other)->create(['name' => 'Other team project']);

        $this->actingAs($user)->get('/projects')
            ->assertInertia(fn (Assert $page) => $page->has('projects', 0));

        $this->actingAs($user)->put('/current-team', ['team_id' => $other->id]);

        $this->actingAs($user)->get('/projects')
            ->assertInertia(fn (Assert $page) => $page->has('projects', 1));
    }

    public function test_the_scope_is_fail_closed_without_a_tenant(): void
    {
        Project::factory()->count(2)->create();

        $this->assertSame(0, Project::count());
        $this->assertSame(2, app(TenantContext::class)->bypass(fn () => Project::count()));
    }
}
