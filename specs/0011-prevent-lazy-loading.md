# 0011 — Support `Model::preventLazyLoading()`

- Status: draft
- Author: Glenn Jacobs
- Created: 2026-05-23
- TODO item: "Support `Model::preventLazyLoading()`"

## Problem

Lunar's cart, pricing, and discount pipelines do a lot of relation walking — `$cartLine->purchasable->product->variants->basePrices`, `$discount->customers`, `$cart->currency`, `$order->lines->purchasable`. Today none of this is guarded. Lazy loads slip through, N+1s ship to production, and the only way they surface is when someone notices a slow request and reads `telescope` / query logs.

Concrete examples currently in `packages/core`:

- `Lunar\Core\Actions\Carts\CalculateLine` lazy-loads `$cartLine->purchasable` and `$cartLine->cart` per line inside the cart-modifier loop (`packages/core/src/Actions/Carts/CalculateLine.php:27-28`).
- `Lunar\Core\Actions\Carts\CreateOrder` lazy-loads `$cart->discounts` and then `$discount->discount` per row when marking discounts as used (`packages/core/src/Actions/Carts/CreateOrder.php:48-50`).
- `Lunar\Core\DiscountTypes\AbstractDiscountType` lazy-loads `$this->discount->customers` and `$cart->currency` inside the per-cart discount-evaluation hot path (`packages/core/src/DiscountTypes/AbstractDiscountType.php:68,79`).
- Filament tables that render relation columns (e.g. `Lunar\Filament\Tables\Order\OrderTable` renders `billingAddress.fullName` and `tags.value` but only `with(['currency'])` is added to the query — `packages/filament/src/Tables/Order/OrderTable.php:33,71,82`).

Lunar v1 inherited this from Laravel's default permissive behaviour. v2 is the right window to flip it: a baseline migrations spec ([[0003-flatten-migrations]]) and a namespace change ([[0002-core-namespace]]) have already shipped, so a behavioural-strictness switch fits the v2.0 envelope and consumers expect to re-read the upgrade guide.

`Model::preventLazyLoading()` is a global static toggle in Laravel — calling it once flips behaviour for every Eloquent model in the process. We can't safely call it from Lunar without controlling the blast radius: a downstream app with its own lazy loads would suddenly throw because Lunar's service provider booted. We need a Lunar-scoped equivalent that strict-checks Lunar's own models only, plus a config-gated way to enable it process-wide for apps that want it.

## Proposal

Three pieces: a Lunar-scoped enforcement on `BaseModel`, a config-gated global enable, and a documented audit + fix pass of the known hotspots before either switch defaults to on.

### A. Lunar-scoped enforcement on `BaseModel`

Every Lunar model extends `Lunar\Core\Base\BaseModel`. Add a `booted()` hook that registers a `relationsLoaded` callback — actually, simpler — override `getRelationshipFromMethod` on `BaseModel` to throw a `Lunar\Core\Exceptions\LunarLazyLoadingViolation` when a relation is resolved on an instance that came from the database (`$exists === true`) and Lunar's strict-relations flag is on.

Shape:

```php
abstract class BaseModel extends Model
{
    protected function getRelationshipFromMethod($method)
    {
        if (LunarLazyLoading::violationsEnabled() && $this->exists && ! $this->wasRecentlyCreated) {
            LunarLazyLoading::handleViolation($this, $method);
        }

        return parent::getRelationshipFromMethod($method);
    }
}
```

`Lunar\Core\Support\LunarLazyLoading` is a small facade-backed manager that:

- Reads `config('lunar.database.prevent_lazy_loading')` (one of `true`, `false`, `'auto'`).
- Resolves `'auto'` to `! app()->isProduction()` — same default as Laravel's `Model::shouldBeStrict()` pattern.
- Holds a swappable violation handler: `LunarLazyLoading::handleViolationUsing(fn (Model $model, string $relation) => …)`.
- Default handler throws `LunarLazyLoadingViolation` (extends `Illuminate\Database\LazyLoadingViolationException` so existing IDE/exception integrations recognise it).

Why scoped on `BaseModel` rather than the global `Model::preventLazyLoading()`:

- Lunar can't safely flip a process-wide flag in a package — downstream apps may rely on lazy loading in their own models, or in other vendor packages.
- Scoped enforcement only fires on Lunar models, so a downstream app that lazy-loads `$user->orders` (where `Order` is Lunar's) still throws, but `$user->profile` (their own model) doesn't.
- A consumer who wants the full Laravel-native global switch can still call `Model::preventLazyLoading()` themselves in their `AppServiceProvider` — Lunar doesn't fight that.

### B. Config switch

Add a `prevent_lazy_loading` key to `packages/core/config/database.php`:

```php
/*
|--------------------------------------------------------------------------
| Prevent Lazy Loading
|--------------------------------------------------------------------------
|
| When enabled, accessing an Eloquent relation on a Lunar model that has
| not been eager-loaded throws a LunarLazyLoadingViolation. Defaults to
| 'auto' — enforced outside production. Set to true to enforce everywhere
| (recommended once your code is clean) or false to disable.
|
*/
'prevent_lazy_loading' => env('LUNAR_PREVENT_LAZY_LOADING', 'auto'),
```

Wired in `LunarServiceProvider::boot()` alongside the other Eloquent-shape setup (`registerObservers`, `registerBuilderMacros`).

### C. Sibling strict toggles

`Model::preventLazyLoading()` is usually paired with two more in Laravel's "strict mode": `preventAccessingMissingAttributes()` and `preventSilentlyDiscardingAttributes()`. They have a smaller blast radius for downstream consumers (they only matter for code authored against Lunar's models) but they also catch real bugs.

Ship both, gated by the same `prevent_lazy_loading` config flag — one switch, one mental model. If a consumer needs them split later we can break the key apart; an extra config knob today is YAGNI.

The relevant call sites:

- `BaseModel::__construct` / `booted` calls into `LunarLazyLoading::apply()`.
- `apply()` (when enabled) calls `Model::preventLazyLoading()` (yes, globally — but documented), `Model::preventAccessingMissingAttributes()`, and `Model::preventSilentlyDiscardingAttributes()`.

Actually no — global `preventLazyLoading()` re-introduces the blast-radius problem. So:

- The lazy-load check stays scoped via the `BaseModel::getRelationshipFromMethod` override.
- `preventAccessingMissingAttributes()` and `preventSilentlyDiscardingAttributes()` are also done scoped: `BaseModel::__get` and `BaseModel::setAttribute` overrides that delegate to a `LunarLazyLoading::guardAttribute()` helper. The default behaviour reuses Laravel's `MissingAttributeException` / `\Illuminate\Database\Eloquent\MassAssignmentException` shapes so test assertions and downstream catches keep working.

This keeps the contract uniform: only Lunar models are strict; downstream models behave however the consumer wants.

### D. Audit + fix the known hotspots

Before the default-on-in-non-prod switch lands, the cart, discount, and order pipelines need cleaning so the test suite passes with the flag flipped. Per-file plan:

1. `Lunar\Core\Actions\Carts\CalculateLine` — caller (`CartLineModifiers` pipeline) already iterates `$cart->lines`; eager-load `purchasable` (morphTo with the morph map already established) and `cart` upstream in `Lunar\Core\Managers\CartSessionManager::calculate()`.
2. `Lunar\Core\Actions\Carts\CreateOrder` — eager-load `discounts.discount` once before the `each()` loop.
3. `Lunar\Core\DiscountTypes\AbstractDiscountType` — accept the cart with `currency` already loaded (cart calculation always loads it); for `$this->discount->customers`, eager-load once at the start of `execute()` rather than re-resolving per cart.
4. `Lunar\Filament\Tables\Order\OrderTable` — extend `modifyQueryUsing(['currency', 'billingAddress', 'tags'])`.
5. `Lunar\Admin\Filament\Resources\ProductResource::getEloquentQuery` — add `with(['brand', 'channels'])` (audit columns first; only load what the table renders).

The implementation PR includes a checklist of the full hotspot sweep; the spec lists the cases above as representative, not exhaustive.

### E. Test suite

- `tests/core/TestCase.php` enables `config(['lunar.database.prevent_lazy_loading' => true])` in `setUp()`. Tests that intentionally exercise lazy-load paths set the config off in their own setup.
- A new `tests/core/Unit/Base/BaseModelLazyLoadingTest.php` covers: violation throws when enabled, no-op when disabled, custom handler is honoured, `'auto'` resolves to non-production behaviour.

### F. Custom violation handler

`LunarLazyLoading::handleViolationUsing()` lets consumers downgrade violations to reports in production without disabling enforcement everywhere — useful for a soft rollout:

```php
// AppServiceProvider::boot()
LunarLazyLoading::handleViolationUsing(function (Model $model, string $relation) {
    report(new LunarLazyLoadingViolation($model, $relation));
});
```

## Alternatives considered

- **Call `Model::preventLazyLoading()` globally in `LunarServiceProvider::boot()`.** Simplest, one line. Rejected — global blast radius means a downstream app's unrelated lazy loads start throwing the moment they upgrade Lunar. That's not a v2 strictness switch, it's a behavioural ambush.
- **Do nothing; document the recommendation.** Rejected — consumers won't apply it, and Lunar's own pipelines have lazy-load N+1s today. The package has to be clean before the recommendation is credible.
- **Only enable in tests via `tests/core/TestCase.php`.** Considered. Useful but insufficient — caught bugs would only surface against contrived test data. Real-world relation paths in dev/local environments wouldn't be checked. Better as a complementary layer alongside the runtime switch.
- **Add `$with` to every model.** Rejected — hardcoded eager loads bloat every query whether the caller needs the relation or not, and they don't help the eager-loaded-elsewhere case (which is most of them). The right fix is per-caller `with()` / `load()`.
- **Lunar trait `EagerLoadable` that auto-eager-loads on Filament queries.** Rejected as scope creep — Filament resources already have a clear seam (`getEloquentQuery` / `modifyQueryUsing`) and the audit in section D covers them.

## Migration impact

- **Database migrations**: none.
- **Public contract surface**:
  - `Lunar\Core\Base\BaseModel` gains overrides of `getRelationshipFromMethod`, `__get`, and `setAttribute`. Subclasses that themselves override these need to call `parent::` (existing Lunar subclasses don't; downstream subclasses may).
  - New public surface: `Lunar\Core\Support\LunarLazyLoading` (facade + manager), `Lunar\Core\Exceptions\LunarLazyLoadingViolation`. Both are documented as the stable extension seam.
- **Behavioural break**: any downstream code lazy-loading on a Lunar model in non-production throws once `prevent_lazy_loading` defaults to `'auto'`. Production is unaffected by default. Setting `LUNAR_PREVENT_LAZY_LOADING=false` restores the old behaviour entirely.
- **Upgrade path for v1.x consumers** (links to [[0001-upgrade-package]]): the upgrade package adds a `Lunar\Upgrade\Notes\PreventLazyLoadingNote` printed by `lunar:upgrade` — a human-read note (no Rector rule, since this is a runtime behaviour change, not a code shape). The note links to a "common eager-load patterns" section in the upgrade guide.
- **Translation / locale impact**: none — exception messages reuse Laravel's existing `LazyLoadingViolationException` wording; no new translated strings.
- **Filament / admin impact**: the hotspot sweep in section D includes `lunarphp/admin` and `lunarphp/filament`. Both must be clean before the default flips. Published resources (per [[0010-filament-self-hosting-parity]]) inherit the eager-loads in their `getEloquentQuery` override; consumers who publish before this spec ships pick up the changes when they re-publish, but their existing copy keeps working (their query just doesn't eager-load the new relations until they update it).

## Open questions

- **Should the default be `'auto'` or `false`?** Leaning `'auto'` — matches Laravel's own `Model::shouldBeStrict()` pattern and gives consumers immediate feedback in dev/local without surprising production. Owner: Glenn, decide before spec moves to `proposed`.
- **Do we need an allow-list / per-relation opt-out?** e.g. `Cart::allowsLazyLoadingFor(['attributes'])` for relations that are genuinely cheap and accessed everywhere. Owner: defer until a real case appears — start strict.
- **Hotspot audit scope** — section D lists representative cases. Before implementation, run the test suite with the flag flipped and capture the full list. Owner: implementer.
- **Telemetry?** Worth emitting a `lunar.lazy_loading_violation` event via the existing `TelemetryService` so consumers can dashboard violations during a soft rollout. Owner: decide during implementation; cheap to add, easy to remove.

## References

- Laravel docs: `Model::preventLazyLoading()`, `Model::shouldBeStrict()`.
- Related specs: [[0002-core-namespace]], [[0003-flatten-migrations]], [[0001-upgrade-package]], [[0010-filament-self-hosting-parity]].
- Hotspot files (line numbers as of `feature/0010-filament-self-hosting-parity`):
  - `packages/core/src/Actions/Carts/CalculateLine.php:27-28`
  - `packages/core/src/Actions/Carts/CreateOrder.php:48-50`
  - `packages/core/src/DiscountTypes/AbstractDiscountType.php:68,79`
  - `packages/filament/src/Tables/Order/OrderTable.php:33,71,82`
  - `packages/admin/src/Filament/Resources/ProductResource.php:143-150`
