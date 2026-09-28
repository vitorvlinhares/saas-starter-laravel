<?php

namespace Tests\Feature\Teams;

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_a_personal_team(): void
    {
        $this->post('/register', [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::where('email', 'ada@example.com')->firstOrFail();

        $this->assertNotNull($user->current_team_id);
        $this->assertTrue($user->currentTeam->personal_team);
        $this->assertSame("Ada's Team", $user->currentTeam->name);
        $this->assertSame(TeamRole::Owner, $user->teamRole($user->currentTeam));
    }

    public function test_a_personal_team_is_created_for_users_without_one(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/dashboard')->assertOk();

        $this->assertNotNull($user->fresh()->current_team_id);
    }

    public function test_users_can_create_teams(): void
    {
        $user = User::factory()->withPersonalTeam()->create();

        $response = $this->actingAs($user)->post('/teams', ['name' => 'Acme']);

        $team = Team::where('name', 'Acme')->firstOrFail();
        $response->assertRedirect(route('teams.show', $team));
        $this->assertSame($team->id, $user->fresh()->current_team_id);
        $this->assertSame(TeamRole::Owner, $team->roleOf($user));
    }

    public function test_users_can_switch_to_a_team_they_belong_to(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $other = Team::factory()->create();
        $other->users()->attach($user, ['role' => TeamRole::Member->value]);

        $this->actingAs($user)
            ->put('/current-team', ['team_id' => $other->id])
            ->assertRedirect(route('dashboard'));

        $this->assertSame($other->id, $user->fresh()->current_team_id);
    }

    public function test_users_cannot_switch_to_a_team_they_do_not_belong_to(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $foreign = Team::factory()->create();

        $this->actingAs($user)
            ->put('/current-team', ['team_id' => $foreign->id])
            ->assertForbidden();
    }

    public function test_non_members_cannot_view_a_team(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $foreign = Team::factory()->create();

        $this->actingAs($user)->get(route('teams.show', $foreign))->assertForbidden();
    }

    public function test_members_cannot_rename_the_team_but_admins_can(): void
    {
        $team = Team::factory()->create();
        $member = User::factory()->create();
        $admin = User::factory()->create();
        $team->users()->attach($member, ['role' => TeamRole::Member->value]);
        $team->users()->attach($admin, ['role' => TeamRole::Admin->value]);

        $this->actingAs($member)
            ->patch(route('teams.update', $team), ['name' => 'Nope'])
            ->assertForbidden();

        $this->actingAs($admin)
            ->patch(route('teams.update', $team), ['name' => 'Renamed'])
            ->assertRedirect();

        $this->assertSame('Renamed', $team->fresh()->name);
    }

    public function test_the_owner_can_delete_a_team_after_confirming_its_name(): void
    {
        $owner = User::factory()->withPersonalTeam()->create();
        $team = Team::factory()->for($owner, 'owner')->create(['name' => 'Acme']);
        $owner->switchTeam($team);

        $this->actingAs($owner)
            ->delete(route('teams.destroy', $team), ['name' => 'wrong'])
            ->assertSessionHasErrors('name');

        $this->actingAs($owner)
            ->delete(route('teams.destroy', $team), ['name' => 'Acme'])
            ->assertRedirect(route('teams.index'));

        $this->assertModelMissing($team);
        $this->assertSame($owner->personalTeam()->id, $owner->fresh()->current_team_id);
    }

    public function test_personal_teams_cannot_be_deleted(): void
    {
        $owner = User::factory()->withPersonalTeam()->create();
        $team = $owner->currentTeam;

        $this->actingAs($owner)
            ->delete(route('teams.destroy', $team), ['name' => $team->name])
            ->assertForbidden();
    }

    public function test_admins_can_change_member_roles_but_not_the_owner_role(): void
    {
        $team = Team::factory()->create();
        $admin = User::factory()->create();
        $member = User::factory()->create();
        $team->users()->attach($admin, ['role' => TeamRole::Admin->value]);
        $team->users()->attach($member, ['role' => TeamRole::Member->value]);

        $this->actingAs($admin)
            ->patch(route('teams.members.update', [$team, $member]), ['role' => 'admin'])
            ->assertRedirect();

        $this->assertSame(TeamRole::Admin, $team->roleOf($member));

        $this->actingAs($admin)
            ->patch(route('teams.members.update', [$team, $team->owner]), ['role' => 'member'])
            ->assertForbidden();
    }

    public function test_members_can_leave_and_admins_can_remove_members(): void
    {
        $team = Team::factory()->create();
        $admin = User::factory()->create();
        $leaver = User::factory()->create();
        $removed = User::factory()->create();

        foreach ([[$admin, 'admin'], [$leaver, 'member'], [$removed, 'member']] as [$user, $role]) {
            $team->users()->attach($user, ['role' => $role]);
        }

        $leaver->switchTeam($team);

        $this->actingAs($leaver)
            ->delete(route('teams.members.destroy', [$team, $leaver]))
            ->assertRedirect(route('dashboard'));

        $this->assertFalse($leaver->fresh()->belongsToTeam($team));
        $this->assertNull($leaver->fresh()->current_team_id);

        $this->actingAs($admin)
            ->delete(route('teams.members.destroy', [$team, $removed]))
            ->assertRedirect();

        $this->assertFalse($removed->fresh()->belongsToTeam($team));

        $this->actingAs($admin)
            ->delete(route('teams.members.destroy', [$team, $team->owner]))
            ->assertForbidden();
    }
}
