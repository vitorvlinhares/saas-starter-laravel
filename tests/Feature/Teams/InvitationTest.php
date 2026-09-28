<?php

namespace Tests\Feature\Teams;

use App\Enums\TeamRole;
use App\Mail\TeamInvitationMail;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admins_can_invite_people_by_email(): void
    {
        Mail::fake();

        $owner = User::factory()->withPersonalTeam()->create();
        $team = $owner->currentTeam;

        $this->actingAs($owner)
            ->post(route('teams.invitations.store', $team), [
                'email' => 'New.Person@Example.com',
                'role' => 'member',
            ])
            ->assertSessionHasNoErrors();

        $invitation = $team->invitations()->firstOrFail();
        $this->assertSame('new.person@example.com', $invitation->email);
        $this->assertSame(64, strlen($invitation->token));

        Mail::assertQueued(TeamInvitationMail::class, fn ($mail) => $mail->hasTo('new.person@example.com')
            && hash('sha256', $mail->token) === $invitation->token);
    }

    public function test_members_cannot_invite(): void
    {
        $team = Team::factory()->create();
        $member = User::factory()->create();
        $team->users()->attach($member, ['role' => TeamRole::Member->value]);

        $this->actingAs($member)
            ->post(route('teams.invitations.store', $team), ['email' => 'x@example.com', 'role' => 'member'])
            ->assertForbidden();
    }

    public function test_the_owner_role_cannot_be_granted_by_invitation(): void
    {
        $owner = User::factory()->withPersonalTeam()->create();

        $this->actingAs($owner)
            ->post(route('teams.invitations.store', $owner->currentTeam), ['email' => 'x@example.com', 'role' => 'owner'])
            ->assertSessionHasErrors('role');
    }

    public function test_the_free_plan_member_limit_is_enforced(): void
    {
        Mail::fake();

        $owner = User::factory()->withPersonalTeam()->create();
        $team = $owner->currentTeam;
        TeamInvitation::factory()->count(2)->for($team)->create(); // 1 member + 2 invites = 3 seats

        $this->actingAs($owner)
            ->post(route('teams.invitations.store', $team), ['email' => 'fourth@example.com', 'role' => 'member'])
            ->assertSessionHasErrors('email');

        Mail::assertNothingQueued();
    }

    public function test_invited_users_can_accept_with_the_matching_email(): void
    {
        $team = Team::factory()->create();
        $user = User::factory()->withPersonalTeam()->create(['email' => 'guest@example.com']);
        TeamInvitation::factory()->for($team)->withToken('plain-token')->create([
            'email' => 'guest@example.com',
            'role' => TeamRole::Admin,
        ]);

        $this->actingAs($user)
            ->get(route('invitations.accept', 'plain-token'))
            ->assertRedirect(route('dashboard'));

        $this->assertSame(TeamRole::Admin, $team->roleOf($user));
        $this->assertSame($team->id, $user->fresh()->current_team_id);
        $this->assertSame(0, $team->invitations()->count());
    }

    public function test_invitations_cannot_be_accepted_by_another_email(): void
    {
        $team = Team::factory()->create();
        $intruder = User::factory()->withPersonalTeam()->create();
        TeamInvitation::factory()->for($team)->withToken('plain-token')->create(['email' => 'guest@example.com']);

        $this->actingAs($intruder)
            ->get(route('invitations.accept', 'plain-token'))
            ->assertForbidden();

        $this->assertFalse($intruder->belongsToTeam($team));
    }

    public function test_expired_invitations_cannot_be_accepted(): void
    {
        $user = User::factory()->withPersonalTeam()->create(['email' => 'guest@example.com']);
        TeamInvitation::factory()->expired()->withToken('plain-token')->create(['email' => 'guest@example.com']);

        $this->actingAs($user)
            ->get(route('invitations.accept', 'plain-token'))
            ->assertStatus(410);
    }

    public function test_guests_are_asked_to_log_in_before_accepting(): void
    {
        TeamInvitation::factory()->withToken('plain-token')->create();

        $this->get(route('invitations.accept', 'plain-token'))
            ->assertRedirect(route('login'));
    }

    public function test_invitations_of_another_team_cannot_be_cancelled(): void
    {
        $owner = User::factory()->withPersonalTeam()->create();
        $foreignInvitation = TeamInvitation::factory()->create();

        $this->actingAs($owner)
            ->delete(route('teams.invitations.destroy', [$owner->currentTeam, $foreignInvitation]))
            ->assertNotFound();

        $this->assertModelExists($foreignInvitation);
    }
}
