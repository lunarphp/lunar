# 0059 — Panel ships its own middleware group

- Status: accepted
- Author: glenn
- Created: 2026-07-22
- TODO item: —

## Problem

The panel already ships its own Inertia middleware (`HandlePanelInertiaRequests`, root view `panel::app`), applied per route group in `PanelServiceProvider::registerRoutes()`. What it does *not* control is the middleware stack it runs inside — it borrows the host application's `web` group:

```php
// config/panel.php
'route_middleware' => ['web'],

// PanelServiceProvider::registerRoutes()
$middleware = config('lunar.panel.route_middleware', ['web']);
Route::middleware([...$middleware, HandlePanelInertiaRequests::class])->prefix($prefix)->group(...);
Route::middleware([...$middleware, Authenticate::class, HandlePanelInertiaRequests::class])->prefix($prefix)->group(...);
```

The `web` group belongs to the consuming app, and downstream apps customise it. The concrete failure:

- Installing Inertia in the host app registers `App\Http\Middleware\HandleInertiaRequests` into the `web` group (the standard `inertia:middleware` + `bootstrap/app.php` append). It then runs on panel routes **layered under** the panel's own Inertia middleware. Two Inertia middlewares on one response means the app's `share()` props leak into panel responses, the app's `rootView` intent conflicts with `panel::app`, and — most damaging — the app's asset `version()` disagrees with the panel's, triggering spurious 409 full-page reloads on the panel.
- More broadly, any host `web` addition (locale switching, tenancy, custom auth, rate limiting) silently applies to panel routes, coupling panel behaviour to unrelated app decisions.

The panel is a self-contained admin surface. It should own its HTTP stack the way the Filament admin does, not inherit whatever the host has bolted onto `web`.

## Proposal

Ship a dedicated panel middleware group assembled from framework primitives, and default the panel to it instead of the host `web` alias.

**1. Register a `lunar.panel` middleware group** in `PanelServiceProvider::boot()` (before `registerRoutes()`), mirroring the framework `web` group minus any app-appended middleware:

```php
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

Route::middlewareGroup('lunar.panel', [
    EncryptCookies::class,
    AddQueuedCookiesToResponse::class,
    StartSession::class,
    ShareErrorsFromSession::class,
    PreventRequestForgery::class,
    SubstituteBindings::class,
]);
```

These are the exact members of this framework version's default `web` group (confirmed against `Illuminate\Foundation\Configuration\Middleware`). They read app-level config (`config/session.php`, cookie domain, CSRF except paths) so session/cookie/CSRF behaviour is preserved — only the app's *appended* middleware is dropped.

**2. Default `route_middleware` to the new group** in `config/panel.php`:

```php
'route_middleware' => ['lunar.panel'],
```

`registerRoutes()` is unchanged — it already spreads `config('lunar.panel.route_middleware')` and appends the panel's own middleware. The fallback default in the `config()` call should also change from `['web']` to `['lunar.panel']` for consistency when config is not published.

A developer who deliberately wants the host `web` stack (or a bespoke stack) still overrides `lunar.panel.route_middleware` in their published config — the seam is preserved, only the default changes.

## Alternatives considered

- **Keep `web`, strip Inertia middleware for panel routes.** Filter any `Inertia\Middleware` subclass out of the resolved group. Rejected: hacky, still leaves every other host `web` customisation applying to panel routes, and depends on reflection over the resolved stack.
- **Do nothing / document the caveat.** Rejected: the double-Inertia-middleware 409 reload is a real bug for any downstream app that also uses Inertia, and "don't customise your web group" is not a constraint we can impose on consumers.
- **Reuse the Filament admin's stack.** Rejected: the panel and the Filament admin are independent packages; the panel should not depend on `lunar/admin` for its HTTP wiring.

## Migration impact

- **Database migrations:** none.
- **Breaking changes to the public contract:** the default value of `lunar.panel.route_middleware` changes from `['web']` to `['lunar.panel']`. For an app on defaults this is transparent and strictly fixes behaviour. An app that has *published* the panel config keeps its existing `['web']` value until it re-publishes — so no silent change under them; document in the release notes that re-publishing (or manually switching to `['lunar.panel']`) is the recommended path. Not a code-level break; no Rector rule required.
- **Upgrade path (v1.x):** the panel is v2-only; no v1 upgrade concern.
- **Translation / locale impact:** none — no user-facing strings.
- **Filament / admin impact:** none — the Filament admin has its own stack.

## Open questions

- ~~Group name `lunar.panel` vs `panel`?~~ Resolved: `lunar.panel`, to avoid collision with any host group named `panel`.
- ~~Does the authenticated panel need `AuthenticateSession`?~~ Resolved: no — the framework default `web` group omits it, so leaving it out preserves current behaviour. Can revisit if "logout other devices" is wanted.

## References

- `packages/panel/src/PanelServiceProvider.php` (`registerRoutes()`)
- `packages/panel/src/Http/Middleware/HandlePanelInertiaRequests.php`
- `packages/panel/config/panel.php`
- `tests/panel/Feature/PanelServiceProviderTest.php` (asserts the current `['web']` default — updates with this change)

## Implementation plan

- [x] Slice 1 — Register the `lunar.panel` middleware group in `PanelServiceProvider` (`registerMiddlewareGroup()`, called from `registerRoutes()`); change the `config/panel.php` default and the `registerRoutes()` fallback to `['lunar.panel']`. `PanelServiceProviderTest` asserts the new default, the exact group membership, and that `panel.dashboard` gathers `HandlePanelInertiaRequests` but not `web`. Existing panel feature tests (724) still pass, confirming session/CSRF/route-model binding intact.
