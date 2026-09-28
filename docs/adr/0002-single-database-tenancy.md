# 2. Single-database tenancy with team_id

## Status

Accepted

## Context

A multi-tenant SaaS needs to isolate each tenant's data. The common approaches are:

1. **Database-per-tenant** — strongest isolation, but every migration, backup, and query touches N databases;
   connection pooling and cross-tenant reporting get expensive fast.
2. **Schema-per-tenant** (e.g. Postgres schemas) — a middle ground, still multiplies migrations and makes
   connection management non-trivial, especially on SQLite (this project's default) which has no schema concept.
3. **Single database, shared tables, a `team_id` column** — simplest to run and to reason about; isolation is
   enforced in the query layer instead of the infrastructure layer.

This is a starter kit meant to run on SQLite by default and scale to Postgres without changing the tenancy model,
so the operational cost of per-tenant databases or schemas isn't worth it for the isolation it buys.

## Decision

Every tenant is a `Team`. Tenant-owned tables (e.g. `projects`) carry a `team_id` foreign key. The
`App\Tenancy\BelongsToTeam` trait adds a global `TeamScope` and stamps `team_id` on create, so tenant-owned models
behave like they're scoped by default without every query repeating `where('team_id', ...)`.

## Consequences

- Migrations, backups, and cross-tenant admin queries stay simple — it's one database.
- Isolation depends entirely on the query scope being applied. See
  [0003](0003-fail-closed-tenant-scope.md) for how we make forgetting the scope fail safe instead of leaking data.
- A tenant that needs true physical isolation (dedicated database, different region) isn't served by this model;
  that would be a different product decision, not a configuration flag.
