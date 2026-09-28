<?php

namespace App\Models;

use App\Billing\Plan;
use App\Enums\TeamRole;
use Database\Factories\TeamFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Cashier\Billable;

class Team extends Model
{
    /** @use HasFactory<TeamFactory> */
    use Billable, HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'personal_team',
    ];

    protected function casts(): array
    {
        return [
            'personal_team' => 'boolean',
            'trial_ends_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * @return HasMany<TeamInvitation, $this>
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(TeamInvitation::class);
    }

    /**
     * Unscoped on purpose: the relation is already constrained by team_id,
     * and it is also used outside the current tenant (e.g. team listings).
     *
     * @return HasMany<Project, $this>
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class)->withoutGlobalScopes();
    }

    public function hasUser(User $user): bool
    {
        return $this->users()->whereKey($user->getKey())->exists();
    }

    public function roleOf(User $user): ?TeamRole
    {
        $role = $this->users()->whereKey($user->getKey())->first()?->pivot?->role;

        return $role !== null ? TeamRole::from($role) : null;
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->user_id === $user->getKey();
    }

    public function plan(): Plan
    {
        $subscription = $this->subscription('default');

        if ($subscription !== null && $subscription->valid()) {
            return Plan::forPrice($subscription->stripe_price) ?? Plan::default();
        }

        return Plan::default();
    }

    /**
     * Stripe customer name and e-mail come from the team and its owner.
     */
    public function stripeName(): ?string
    {
        return $this->name;
    }

    public function stripeEmail(): ?string
    {
        return $this->owner?->email;
    }
}
