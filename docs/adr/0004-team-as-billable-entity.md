# 4. Team as the billable entity

## Status

Accepted

## Context

Laravel Cashier expects a single "billable" model — by default, `User`. In a multi-tenant app where a `Team` can
have several members, billing per-user doesn't match how the product actually works: a subscription, its plan
limits, and its usage belong to the team, not to whichever member happens to be signed in.

## Decision

`App\Models\Team` is the Cashier billable (`Cashier::useCustomerModel(Team::class)` in `AppServiceProvider`).
Subscriptions, plan limits (`config/plans.php`, `App\Billing\Plan`), and usage counters are all resolved from the
current team, not the current user. Cashier's own migrations are not published — this project ships its own
`subscriptions`/`subscription_items` migrations with a `team_id` column instead of Cashier's default `user_id`.

Authorization for billing actions goes through `TeamPolicy::manageBilling`, which only the team owner passes.

## Consequences

- `$team->plan()`, `$team->subscribed(...)`, and Stripe Checkout/portal all operate on the team, which matches how
  the product is sold (per team, not per seat-holder).
- Any code that assumes `Auth::user()` is billable (a common assumption in Cashier examples and tutorials) needs
  to be adapted to use `app(TenantContext::class)->team()` instead.
- Publishing Cashier's default migrations would recreate a `user_id`-based schema and conflict with this one; see
  the "Don'ts" section of `AGENTS.md`.
