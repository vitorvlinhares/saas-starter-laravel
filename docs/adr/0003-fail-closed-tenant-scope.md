# 3. Fail-closed tenant scope

## Status

Accepted

## Context

With tenancy enforced by a `team_id` column and a query scope (see [0002](0002-single-database-tenancy.md)), the
one bug that matters most is: what happens when the scope runs with no tenant in context? A queue job without a
team, a console command run without `--team`, a middleware that didn't fire — any of these could ask
`Project::all()` with no team set.

Two options:

1. **Fail-open** — no team in context means the query runs unscoped and returns every tenant's rows. Convenient for
   debugging, catastrophic if it ever happens in a real request.
2. **Fail-closed** — no team in context means the query returns nothing.

## Decision

`App\Tenancy\TeamScope` is fail-closed: if `App\Tenancy\TenantContext` has no team set, every scoped query returns
an empty result rather than an unscoped one. Code that genuinely needs to cross tenants (seeders, admin tooling,
scheduled reports) must say so explicitly via `app(TenantContext::class)->bypass(fn () => ...)`.

## Consequences

- A missing tenant context turns into "this looks empty" (a bug report) instead of "this leaked another
  customer's data" (an incident). We consider that trade worth making even though it can make a misconfigured
  code path harder to notice at a glance.
- Anything that legitimately needs cross-tenant access has to opt in explicitly, which makes those code paths
  easy to grep for and review.
- `App\Http\Middleware\EnsureTeamContext` is responsible for setting the context on every authenticated request;
  if it's missing from a route's middleware stack, that route's tenant-scoped queries will silently return
  nothing rather than leaking data, which is the point.
