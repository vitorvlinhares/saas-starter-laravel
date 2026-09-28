<?php

namespace App\Http\Controllers;

use App\Billing\Plan;
use App\Tenancy\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class BillingController extends Controller
{
    public function __construct(private readonly TenantContext $tenant) {}

    public function show(): Response
    {
        $team = $this->tenant->team();
        $subscription = $team->subscription('default');

        return Inertia::render('billing/index', [
            'team' => ['id' => $team->id, 'name' => $team->name],
            'currentPlan' => $team->plan()->key,
            'plans' => array_map(fn (Plan $plan) => $plan->toArray(), Plan::all()),
            'subscription' => $subscription ? [
                'status' => $subscription->stripe_status,
                'on_grace_period' => $subscription->onGracePeriod(),
                'ends_at' => $subscription->ends_at?->toIso8601String(),
            ] : null,
            'usage' => [
                'members' => $team->users()->count(),
                'projects' => $team->projects()->count(),
            ],
            'currency' => strtoupper(config('cashier.currency', 'usd')),
            'canManage' => Gate::allows('manageBilling', $team),
            'stripeConfigured' => filled(config('cashier.secret')),
        ]);
    }

    /**
     * Start a Stripe Checkout session, or swap the plan of an existing subscription.
     */
    public function checkout(Request $request): HttpResponse
    {
        $team = $this->tenant->team();

        Gate::authorize('manageBilling', $team);

        $validated = $request->validate([
            'plan' => ['required', Rule::in(array_map(fn (Plan $plan) => $plan->key, Plan::all()))],
        ]);

        $plan = Plan::find($validated['plan']);

        if ($plan->isFree() || blank($plan->priceId)) {
            throw ValidationException::withMessages([
                'plan' => 'This plan cannot be purchased. Check the STRIPE_PRICE_* variables.',
            ]);
        }

        $subscription = $team->subscription('default');

        if ($subscription !== null && $subscription->valid()) {
            $subscription->swap($plan->priceId);

            return to_route('billing');
        }

        $checkout = $team->newSubscription('default', $plan->priceId)->checkout([
            'success_url' => route('billing').'?checkout=success',
            'cancel_url' => route('billing').'?checkout=cancelled',
        ]);

        return Inertia::location($checkout->url);
    }

    public function portal(): HttpResponse
    {
        $team = $this->tenant->team();

        Gate::authorize('manageBilling', $team);

        return Inertia::location($team->billingPortalUrl(route('billing')));
    }
}
