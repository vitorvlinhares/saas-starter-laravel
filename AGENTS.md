# saas-starter-laravel

Multi-tenant SaaS starter by Vitor Linhares (LV² Tech & Strategy). Public portfolio project: code, commits, README and docs in English.

## Stack
- Laravel 12, PHP 8.3+ (CI uses 8.4), Inertia 2 + React + TypeScript, shadcn/ui, Tailwind v4, Ziggy `route()`.
- Laravel Cashier 16 (Stripe, test mode). SQLite by default; Postgres/Redis/Mailpit via Docker (optional).
- Tests: PHPUnit class style (`tests/Feature/*Test.php`, `RefreshDatabase`). Not Pest.
- Frontend unit tests: Vitest + Testing Library (`resources/js/**/*.test.{ts,tsx}`), kept small on purpose —
  hooks/utilities/components only. Page-level behavior is already covered by PHPUnit's `assertInertia()`.

## Commands
- `composer install` / `npm install`
- `php artisan migrate:fresh --seed` (demo login: test@example.com / password)
- `php artisan test` (must stay green)
- `npm run test` (frontend unit tests, must stay green)
- `composer run dev` (server + vite + queue)
- `vendor/bin/pint`, `npm run lint`, `npm run format`, `npx tsc --noEmit`

## Architecture (read before changing tenancy or billing)
- **Tenant = Team.** Single database, `team_id` column. See `docs/adr/`.
- `App\Tenancy\TenantContext` (scoped singleton) holds the current team. Falls back to the authenticated user's `current_team_id`.
- `App\Tenancy\BelongsToTeam` trait: adds `TeamScope` and stamps `team_id` on create. Use it on every tenant-owned model (example: `App\Models\Project`).
- `TeamScope` is **fail-closed**: no team in context -> query returns nothing. Use `app(TenantContext::class)->bypass(fn () => ...)` for cross-tenant work (seeders, admin, reports).
- `EnsureTeamContext` middleware (alias `team`) sets the context, repairs a stale `current_team_id` and creates a personal team if the user has none. Every authenticated app route goes through `['auth', 'team']`.
- Roles: `App\Enums\TeamRole` (owner, admin, member). Authorization in `App\Policies\TeamPolicy` (auto-discovered): view, update, manageMembers, delete, manageBilling (owner only).
- Invitations: token stored as SHA-256 hash, plain token only in the e-mail link. Accept requires the logged-in user's e-mail to match. Expire after 7 days.
- Billing: `Team` is the Cashier billable (`Cashier::useCustomerModel(Team::class)` in AppServiceProvider). Custom migrations use `team_id` (Cashier's default migrations are NOT published on purpose). Plans in `config/plans.php`, value object `App\Billing\Plan`, `$team->plan()` resolves the active plan; limits (members, projects) enforced in controllers. Webhook route `/stripe/webhook` comes from Cashier; CSRF excluded in `bootstrap/app.php`.
- Routes: `routes/web.php` (dashboard), `routes/teams.php` (teams, members, invitations, projects, billing), `routes/settings.php`, `routes/auth.php`.
- Shared Inertia props (`HandleInertiaRequests`): `auth.user`, `currentTeam` {id, name, personal_team, role, can_manage, plan}, `teams` [{id, name, personal_team}].

## Frontend conventions
- Follow the existing pages (`resources/js/pages/settings/password.tsx`): `AppLayout` with breadcrumbs, `HeadingSmall`, `useForm` + `route()`, `Label`/`Input`/`InputError`, `Button`, headlessui `Transition` for "Saved".
- Only use components that exist in `resources/js/components/ui`.
- Types live in `resources/js/types/index.ts`.

## Brand (LV² Tech & Strategy)
- Logos in `resources/brand/` (SVG). Symbol = "LV" + two carmine bars (the "II").
- Colors: Slate `#101419` (bg dark), Graphite `#181E26` (raised), White `#F1F3F7` (ink), Silver `#8A93A3` (muted), Line `#262D38`, Carmine `#E8475A` (accent, dark theme) / `#C8283F` (accent, light theme). Light theme: bg `#FFFFFF`, raised `#F4F5F7`, ink `#101419`, muted `#5E6573`, line `#E2E5EA`.
- Fonts: Michroma (display: logo, big numbers, short titles), Geist (UI/body), Geist Mono (labels, code). All on Google Fonts.
- Rules: carmine is the ONLY accent, at most one highlight per screen besides the logo. Square corners on cards, 4px radius only on buttons/inputs. Thin 1px lines instead of shadows. No gradients, no emojis, nothing that looks generic/AI-made.

## Don'ts
- Don't publish Cashier's default migrations (they use user_id).
- Don't query tenant models with `withoutGlobalScopes()` outside `TenantContext::bypass`.
- Never commit `.env` or real Stripe keys.
