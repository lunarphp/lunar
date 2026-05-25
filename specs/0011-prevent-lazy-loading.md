# 0011 — Make Lunar safe under `Model::preventLazyLoading()`

- Status: draft
- Author: Glenn Jacobs
- Created: 2026-05-23
- TODO item: "Support `Model::preventLazyLoading()`"

## Problem

Lunar's cart, pricing, and discount pipelines do a lot of relation walking — `$cartLine->purchasable->product->variants->basePrices`, `$discount->customers`, `$cart->currency`, `$order->lines->purchasable`. Today none of this is guarded. Lazy loads slip through, N+1s ship to production, and the only way they surface is when someone notices a slow request and reads `telescope` / query logs.

Concrete examples currently in `packages/core`:

- `Lunar\Core\Actions\Carts\CalculateLine` reads `$cartLine->purchasable` and `$cartLine->cart` per line inside the cart-modifier loop.
- `Lunar\Core\DiscountTypes\AbstractDiscountType` reads `$this->discount->customers` and `$cart->currency` inside the per-cart discount-evaluation hot path.
- Filament tables that render relation columns (e.g. `Lunar\Filament\Tables\Order\OrderTable` renders `billingAddress.fullName` and `tags.value` but only `with(['currency'])` is added to the query).
- Admin / Filament pages that show a Collection's `breadcrumb` accessor (which walks `ancestors`) without eager-loading it.

Laravel already ships `Model::preventLazyLoading()` (and the wider `Model::shouldBeStrict()`). It's a global static; a single line in a host app's `AppServiceProvider` and every Eloquent model — Lunar's included — throws on lazy loads. Consumers who care about discipline already know how to enable it.

The thing Lunar owes them is that **its own pipelines pass under that flag**. If a consumer flips `Model::preventLazyLoading()` and the cart calculate flow explodes, the recommendation isn't credible.

That's the contract this spec defines.

## Proposal

Two pieces: (a) Lunar's pipelines are clean under Laravel's stock strict-mode flag, (b) the upgrade docs point consumers at that flag and call out Lunar's commitment.

There is **no Lunar-specific switch, exception, manager, or BaseModel override**. Earlier drafts of this spec proposed a scoped `LunarLazyLoading` facade on the theory that a consumer might want strict mode on Lunar only. In practice that case is rare; the real desire is global strictness with Lunar trusted to participate. Maintaining a parallel mechanism would only make the public surface harder to evolve, with no benefit Laravel's own switch doesn't already provide.

### A. Audit + eager-load the known hotspots

Per-file plan (representative; the implementation PR includes the full sweep):

1. `Lunar\Core\Pipelines\Cart\CalculateLines` — `loadMissing(['lines.purchasable', 'currency'])` and `setRelation('cart', $cart)` per line before the per-line pipeline runs.
2. `Lunar\Core\DiscountTypes\AbstractDiscountType` — `loadMissing('customers')` on the discount when `with()` is called; `loadMissing('currency')` on the cart at the top of `checkDiscountConditions()`.
3. `Lunar\Filament\Tables\Order\OrderTable` — extend `modifyQueryUsing` to `['currency', 'billingAddress', 'tags']`.
4. `Lunar\Filament\Tables\Product\ProductTable` — add `modifyQueryUsing(['brand', 'productType'])`.
5. `Lunar\Filament\RelationManagers\Discount\CollectionLimitationRelationManager` — `with('ancestors')` on the query so `$record->breadcrumb` (which walks the nested-set relation) doesn't lazy-load.
6. `Lunar\Filament\Forms\Components\DiscountTargetSelect` — eager-load `ancestors` on both the search builder and the `getOptionLabelUsing` find call.
7. `Lunar\Filament\Forms\Components\CollectionSelect` — wire `modifyOptionsQueryUsing` with `with('ancestors')` so the shared `SearchesLunarRecords` trait stays generic.
8. `Lunar\Filament\Widgets\Collections\CollectionTreeView` — `with('ancestors')` on the move-action search builder.
9. `Lunar\Filament\Widgets\Products\ProductOptionsWidget` — replace `$record->variants->load(...)` with `$record->loadMissing(['variants.basePrices.currency', 'variants.basePrices.priceable', 'variants.values.option'])`.
10. `Lunar\Admin\Filament\Resources\ProductResource\Pages\ManageProductCollections` and `ManageBrandCollections` — add `modifyQueryUsing(fn ($q) => $q->with('ancestors'))`.
11. `Lunar\Admin\Filament\Resources\CollectionResource::getCollectionBreadcrumbs` — `loadMissing('group')` before reading it.

The remaining audit (clicking through every panel page with `Model::preventLazyLoading()` on; exercising every storefront helper via feature tests) is the next step before this spec moves to `accepted`.

### B. Hardening the `Price` cast

`Lunar\Core\Base\Casts\Price::get()` reaches `$model->priceable->unit_quantity ?? $model->unit_quantity ?? 1`. Two problems surface under strict mode:

- `priceable` is only a relation on `Lunar\Core\Models\Price`. On `OrderLine`, `Order`, `Transaction` it's a missing attribute — fine when Laravel's `preventAccessingMissingAttributes` is off, a thrown exception when it's on.
- On the `Price` model the relation isn't always eager-loaded, so the access lazy-loads.

Fix: gate the `priceable` read on `relationLoaded('priceable')`; otherwise read `unit_quantity` from `getAttributes()`; otherwise default to `1`. The cast no longer reaches for relations or attributes it isn't sure are there.

### C. Documentation

A short README / upgrade note:

> Lunar's pipelines are written to run safely under Laravel's `Model::preventLazyLoading()`. We recommend enabling it in dev and test:
>
> ```php
> // AppServiceProvider::boot()
> Model::preventLazyLoading(! app()->isProduction());
> ```
>
> If you find a lazy load coming from Lunar code, please open an issue — that's a bug.

That's the whole consumer-facing contract. No Lunar facade, no env var.

## Alternatives considered

- **Ship a Lunar-scoped switch (`LunarLazyLoading` manager / facade / config / `BaseModel` overrides).** Rejected after a first-pass implementation. The premise — "consumers might want strict mode on Lunar only" — didn't survive contact with reality. The realistic case is *global* strictness with Lunar trusted to participate. A scoped layer just doubles the public surface (new facade, new exception subclass, new config key, new override pattern subclassers need to be aware of) for no value Laravel's stock flag doesn't already give.
- **Call `Model::preventLazyLoading()` globally in `LunarServiceProvider::boot()`.** Rejected — that's the consumer's choice, not Lunar's. Forcing the flag on at boot is exactly the blast-radius problem we don't want.
- **Do nothing; document the recommendation.** Insufficient on its own. Lunar's own pipelines have lazy-load N+1s today; the recommendation isn't credible until they're cleaned up. The audit IS the value this spec ships.
- **Add `$with` to every model.** Rejected — hardcoded eager loads bloat every query whether the caller needs the relation or not, and they don't help the eager-loaded-elsewhere case (which is most of them). The right fix is per-caller `with()` / `load()`.

## Migration impact

- **Database migrations**: none.
- **Public contract surface**: none added. Several internal call sites gain explicit eager-loads — those are bug fixes, not contract changes. Downstream consumers see fewer queries and no new exceptions.
- **Behavioural break**: none. Default behaviour is identical; consumers who already had `Model::preventLazyLoading()` on suddenly stop seeing Lunar-originated violations.
- **Upgrade path for v1.x consumers**: no Rector rule needed — the recommendation is "enable Laravel's flag yourself", which works the same on every Laravel version Lunar has ever supported.
- **Filament / admin impact**: the hotspot sweep in section A covers `lunarphp/admin` and `lunarphp/filament`. Published resources (per spec 0010) inherit the eager-loads in their `getEloquentQuery` / `modifyQueryUsing` override; consumers who publish before this spec ships pick up the changes when they re-publish, but their existing copy keeps working (their query just doesn't eager-load the new relations until they update it).

## Open questions

- **Full audit completion** — section A covers the violations found via host-app browsing so far. Before this spec moves to `accepted`, a methodical click-through of the panel (resources index, every relation manager page, every widget, every action modal, global search) plus a feature-test pass for the cart/order/discount/checkout flows under `Model::preventLazyLoading()` needs to land. Owner: implementer.
- **Telemetry?** Worth emitting a `lunar.lazy_loading_violation` event via the existing `TelemetryService` so consumers can dashboard violations during a soft rollout. Decided: defer — Laravel's `Model::handleLazyLoadingViolationUsing()` already gives consumers a hook; we don't need a Lunar-specific event for it.

## References

- Laravel docs: `Model::preventLazyLoading()`, `Model::shouldBeStrict()`, `Model::handleLazyLoadingViolationUsing()`.
- Related specs: [[0002-core-namespace]], [[0003-flatten-migrations]], [[0010-filament-self-hosting-parity]].
