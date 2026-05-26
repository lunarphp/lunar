# 0016 — Service-layer dependency injection

- Status: implemented
- Author: Glenn Jacobs
- Created: 2026-05-26
- TODO item: "Ensure all service-layer classes are DI'd"

## Problem

Lunar's service-layer composition is half-DI'd and half-locator. The container is used inconsistently across the same call sites, dependencies are pulled at the point of use instead of injected at the boundary, and there is no convention a consumer can follow to swap a service.

Concrete examples in `packages/core/src/` as of this spec:

- **Actions live outside the container by convention.** `AbstractAction::make()` is defined as `app(static::class)` and `AbstractAction::run(...)` as `static::make()->execute(...)`. The class itself takes no constructor dependencies, so every collaborator is fetched mid-`execute()` via `app(...)`, a facade, or a `config('lunar.*', Default::class)` lookup. Nothing about an action's signature documents what it depends on.
- **Sub-action resolution mixes container and config indirection.** `Lunar\Core\Models\Cart::add` does `app(config('lunar.cart.actions.add_to_cart', AddOrUpdatePurchasable::class))`. Inside `AddOrUpdatePurchasable::execute`, the same pattern recurses for `get_existing_cart_line`. Eight action keys in `config/cart.php` (`add_to_cart`, `get_existing_cart_line`, `update_cart_line`, `remove_from_cart`, `add_address`, `set_shipping_option`, `order_create`, plus `fingerprint_generator`) duplicate what container binding already provides — and they shadow it: even if a consumer binds `AddOrUpdatePurchasable::class` in the container, the config-string indirection skips that binding.
- **Managers reach for global state in constructors.** `Managers\PricingManager::__construct` calls `Auth::check()` and `Auth::user()` directly. It cannot be instantiated in a test without booting the auth stack, and the user is captured once at resolution time rather than read on demand — so a `PricingManager` resolved in middleware before login carries `null` for the rest of the request even after the user logs in.
- **Generators self-bootstrap.** `Generators\UrlGenerator::__construct` calls `Language::getDefault()` — a database query — at construction. Resolving `UrlGenerator` from the container before the languages table exists (e.g. early in a migration) blows up. `UrlGenerator` is not bound to the container at all; consumers `new` it directly.
- **Drivers depend on facades, not contracts.** `Drivers\SystemTaxDriver` reaches `Facades\Taxes`, `Facades\PriceCalculator`, and `Spatie\LaravelBlink\BlinkFacade` inline. The driver cannot be unit-tested without booting the full container, and a consumer writing a `CustomTaxDriver` has to know which facades the base implementation reads.
- **Coupon validator resolves through config inside the manager.** `Managers\DiscountManager::validateCoupon` does `app(config('lunar.discounts.coupon_validator', CouponValidator::class))->validate($coupon)`. There is no `CouponValidator` binding in `LunarServiceProvider`; the config key is the only swap seam, and it cannot be overridden by binding the contract.
- **No documented convention.** Half the bindings in `LunarServiceProvider::register()` follow `bind(Interface::class, fn ($app) => $app->make(Impl::class))`, half follow `singleton(...)`, one uses `bind(...)` with parameters, and one uses `singleton(InterfaceA::class, ImplA::class)` (string form). A maintainer adding a new service has no obvious pattern to copy.

Net effect: extending or testing any service-layer class requires reading its body to discover the seams. The contract surface a consumer can rely on (interfaces + container bindings) is smaller than the actual seam surface (config keys, facades, `app()` calls). Swappability is partial and order-dependent — config wins over container binding for actions, container binding wins over config for managers, and there is no rule that says which.

## Proposal

Adopt a single convention across `packages/core/src/`: every service-layer class is constructed by the container with its dependencies declared on the constructor; every public service seam is an interface bound in `LunarServiceProvider`; configuration is for *values*, the container is for *substitutions*. Apply the convention end-to-end and delete the config-string substitution layer that duplicates it.

### A. Definition of "service-layer class"

For this spec, a service-layer class is any class under `packages/*/src/` whose role is to *do work* — orchestrate state changes, run a calculation, build an entity, drive an external API. Concretely:

- `Actions/`, `Managers/`, `Drivers/`, `Generators/`, `Orders/`, `Pricing/`, `Search/`, `Telemetry/`, `Validation/` (where the validator is a class, not a `BaseValidator` subclass invoked via Laravel's `Validator` facade), `Listeners/`, `Observers/`, `Pipelines/`.
- The `Manifests/`, `Modifiers/`, `Media/` registries are already singletons and follow the convention — they're cited here so the rule covers them too.

Explicitly **not** service-layer for this spec: `Models/`, `DataObjects/`, `ValueObjects/`, `Casts/`, `Concerns/`, `Contracts/`, `Enums/`, `DataTypes/`, `FieldTypes/`, `Exceptions/`, `Rules/` (Laravel rule objects are instantiated by the validator), `Facades/`.

### B. The convention

For each service-layer class:

1. **Declare an interface in `Contracts/`** if the class is part of the public surface (overridable by consumers) or has more than one implementation. Drop the `Interface` suffix (per spec 0013). The interface lists only the methods consumers are expected to call.
2. **Constructor injection only.** Every collaborator the class needs in `execute()` / `handle()` / `get()` is declared on the constructor with a type hint, using PHP 8 promoted properties (per project PHP rules). No `app(...)`, `App::make(...)`, `resolve(...)`, or facade lookup inside method bodies for collaborators the class always uses. Per-request runtime arguments (a cart, a purchasable, a quantity) stay on the method signature.
3. **Bind in `LunarServiceProvider::register()`.** One pattern, applied uniformly:
   ```php
   $this->app->singleton(Foo::class, DefaultFoo::class);
   ```
   when the binding is stateless and shared, or
   ```php
   $this->app->bind(Foo::class, DefaultFoo::class);
   ```
   when a fresh instance per resolve is required (anything that holds per-call state). The closure form (`fn ($app) => $app->make(DefaultFoo::class)`) is reserved for the case where the concrete class needs runtime parameters that the container cannot infer.
4. **Swap-via-binding, not swap-via-config.** Consumers override a service by binding its interface to their implementation in their own service provider. Config keys that name a class to resolve are removed; config keys that name a *value* (e.g. `lunar.orders.reference_format.length`) stay.
5. **Resolve at the boundary.** Callers of a service either (a) inject the service via their own constructor, or (b) use the Lunar facade backed by the container binding. Inline `app(SomeService::class)` calls in business code are a smell to be removed when touched.

### C. Concrete migrations

#### C.1 — Actions

`AbstractAction` is deleted. The `::make()` / `::run()` shortcuts are deleted. Every action becomes a plain class with constructor-injected dependencies and a single public method named after what it does.

Before:

```php
// Actions/Carts/AddOrUpdatePurchasable.php
final class AddOrUpdatePurchasable extends AbstractAction
{
    public function execute(CartContract $cart, Purchasable $purchasable, int $quantity = 1, array $meta = []): self
    {
        $existing = app(
            config('lunar.cart.actions.get_existing_cart_line', GetExistingCartLine::class)
        )->execute(cart: $cart, purchasable: $purchasable, meta: $meta);
        // ...
    }
}
```

After:

```php
// Actions/Carts/AddOrUpdatePurchasable.php implements Contracts\AddsOrUpdatesPurchasable
final class AddOrUpdatePurchasable implements AddsOrUpdatesPurchasable
{
    public function __construct(
        private GetsExistingCartLine $getsExistingCartLine,
    ) {}

    public function execute(CartContract $cart, Purchasable $purchasable, int $quantity = 1, array $meta = []): CartContract
    {
        $existing = $this->getsExistingCartLine->execute($cart, $purchasable, $meta);
        // ...
    }
}
```

Action return types tighten: `->then(fn () => ...)` and the `$passThrough` indirection on `AbstractAction` go away. Each action returns the value its callers actually use (a `Cart`, an `Order`, a `bool`); the `then(...)` post-action callback that `Cart::add` relies on becomes a regular method chain in the caller.

`Cart::add`, `Cart::remove`, `Cart::updateLine`, `Cart::associate`, `Cart::addAddress`, `Cart::setShippingOption`, `Cart::createOrder`, `Cart::fingerprint` and `Cart::canCreateOrder` stop reading `config('lunar.cart.actions.*')`. They resolve the relevant action interface from the container and call it. The `config/cart.php` `actions` block is deleted in this spec; the `validators` block stays (it's a list of multiple pipes, which is a value, not a substitution).

Action interfaces live in `Contracts/Actions/` to keep the directory shallow:

| Interface | Default implementation |
| --- | --- |
| `AddsOrUpdatesPurchasable` | `Actions\Carts\AddOrUpdatePurchasable` |
| `GetsExistingCartLine` | `Actions\Carts\GetExistingCartLine` |
| `UpdatesCartLine` | `Actions\Carts\UpdateCartLine` |
| `RemovesPurchasable` | `Actions\Carts\RemovePurchasable` |
| `AddsAddress` | `Actions\Carts\AddAddress` |
| `AssociatesUser` | `Actions\Carts\AssociateUser` |
| `SetsShippingOption` | `Actions\Carts\SetShippingOption` |
| `CreatesOrder` | `Actions\Carts\CreateOrder` |
| `GeneratesFingerprint` | `Actions\Carts\GenerateFingerprint` |
| `CalculatesLine` | `Actions\Carts\CalculateLine` |
| `CalculatesLineSubtotal` | `Actions\Carts\CalculateLineSubtotal` |
| `MergesCart` | `Actions\Carts\MergeCart` |
| `RefundsOrder` | `Actions\Orders\RefundOrder` |
| `CapturesOrder` | `Actions\Orders\CaptureOrder` |
| `UpdatesOrderStatus` | `Actions\Orders\UpdateOrderStatus` |
| `MarksOrderAsShipped` | `Actions\Orders\MarkOrderAsShipped` |
| `GeneratesOrderReference` | `Actions\Orders\GenerateOrderReference` |
| `DuplicatesProduct` | `Actions\Products\DuplicateProduct` |
| `AdjustsStock` | `Actions\Products\AdjustStock` |
| `UpdatesProductStatus` | `Actions\Products\UpdateProductStatus` |
| `MapsVariantsToProductOptions` | `Actions\Products\MapVariantsToProductOptions` |
| `GetsTaxZone` | `Actions\Taxes\GetTaxZone` |
| `GetsTaxZoneCountry` | `Actions\Taxes\GetTaxZoneCountry` |
| `GetsTaxZonePostcode` | `Actions\Taxes\GetTaxZonePostcode` |
| `GetsTaxZoneState` | `Actions\Taxes\GetTaxZoneState` |
| `SortsProducts` | `Actions\Collections\SortProducts` (the default; `SortProductsByPrice` / `SortProductsBySku` register as named bindings under the same interface or stay class-keyed — decided in implementation) |
| `CreatesChildCollection` | `Actions\Collections\CreateChildCollection` |
| `CreatesRootCollection` | `Actions\Collections\CreateRootCollection` |
| `DeletesCollection` | `Actions\Collections\DeleteCollection` |
| `MovesCollection` | `Actions\Collections\MoveCollection` |
| `CreatesCurrencyPrices` | `Actions\Currencies\CreateCurrencyPrices` |

Every binding goes in `LunarServiceProvider::register()` in a dedicated `registerActions()` method that walks a map. The interface name is the swap seam — bind your own implementation, the rest of the system picks it up.

#### C.2 — Managers

| Manager | Change |
| --- | --- |
| `Managers\PricingManager` | Constructor takes `AuthManager` instead of touching the `Auth` facade. `Auth::check()` / `Auth::user()` move into `get()` so the user is read at call time, not capture time. The eager `$this->user = Auth::user()` in the constructor is removed. |
| `Managers\DiscountManager` | Constructor takes `CouponValidator` (the contract, new — see C.5). `validateCoupon()` calls `$this->couponValidator->validate(...)` instead of `app(config(...))->validate(...)`. The `lunar.discounts.coupon_validator` config key is deleted; consumers bind `CouponValidator::class` in their service provider. |
| `Managers\CartSessionManager` | Already DI'd. No change. |
| `Managers\StorefrontSessionManager` | Already DI'd. No change. |
| `Managers\TaxManager`, `Managers\PaymentManager` | Already use the container via `Illuminate\Support\Manager::buildProvider`. No change. |

#### C.3 — Generators / Orders

`Generators\UrlGenerator`: drop the `Language::getDefault()` call from the constructor. Read the default language lazily in `handle()` (or have the caller pass the language in). Bind `UrlGenerator` in `LunarServiceProvider::register()` as a singleton so observers can resolve it through the container.

`Orders\ReferenceGenerator`: already bound. No change.

#### C.4 — Drivers

`Drivers\SystemTaxDriver` constructor takes `GetsTaxZone` (the new action contract), `PriceCalculatorInterface`, and the Blink cache repository (typed against `Spatie\LaravelBlink\Blink`, not the facade). The inline `Taxes::` / `PriceCalculator::` / `Blink::` calls are replaced with `$this->...` accesses. The driver is still built by `TaxManager::buildProvider`, which already routes through `container->make`, so DI just works.

#### C.5 — Validation

`Validation\CouponValidator` gains a `Contracts\CouponValidator` interface in `Contracts/` and is bound in `LunarServiceProvider::register()`:

```php
$this->app->bind(CouponValidatorContract::class, CouponValidator::class);
```

`Rules\ValidCoupon` switches from `app(config('lunar.discounts.coupon_validator', CouponValidator::class))` to `app(CouponValidatorContract::class)` (or constructor-injected when the rule is constructed in a context that supports it).

#### C.6 — Cart model orchestration

`Models\Cart` stops being the action dispatcher. The eight `app(config('lunar.cart.actions.*', Default::class))->execute(...)` calls become method calls on injected action contracts. Because Eloquent models cannot take constructor dependencies cleanly, `Cart` resolves each action interface from the container at the call site:

```php
public function add(Purchasable $purchasable, int $quantity = 1, ...): Cart
{
    foreach (config('lunar.cart.validators.add_to_cart', []) as $validator) {
        app($validator)->using(...)->validate();
    }

    app(AddsOrUpdatesPurchasable::class)->execute($this, $purchasable, $quantity, $meta);

    return $refresh ? $this->refresh()->recalculate() : $this;
}
```

The validator loop continues to use `app(...)` because validators are a *list* configured per cart event (see C.7) — there is no single binding to resolve.

This is a smaller end-state than full constructor injection on the model, but it (a) removes the config-string indirection that duplicates container binding, (b) lets consumers swap actions through `app()->bind(Interface::class, MyImpl::class)`, and (c) keeps Eloquent's model construction unchanged.

#### C.7 — Validators (the cart pipeline ones)

The `lunar.cart.validators.*` config keys stay (they're lists, not singletons). The validator classes themselves (`Validation\Cart\CartLineQuantity`, etc.) gain constructor injection where they currently use facades. They are resolved via `app($validator)` per call — a list-of-implementations is a config concern.

### D. Binding registration shape

`LunarServiceProvider::register()` ends up with three named helper methods, each containing a flat map. The body of `register()` becomes mostly the calls to these helpers plus a handful of one-offs (modifiers, manifests, telemetry). Example shape:

```php
protected function registerActions(): void
{
    $bindings = [
        AddsOrUpdatesPurchasable::class => Actions\Carts\AddOrUpdatePurchasable::class,
        GetsExistingCartLine::class     => Actions\Carts\GetExistingCartLine::class,
        // ...
    ];

    foreach ($bindings as $contract => $concrete) {
        $this->app->bind($contract, $concrete);
    }
}

protected function registerManagers(): void { /* PricingManager, DiscountManager, TaxManager, PaymentManager, CartSession, StorefrontSession */ }

protected function registerServices(): void { /* PriceCalculator, PriceFormatter, UrlGenerator, ReferenceGenerator, CouponValidator, TelemetryService */ }
```

The convention's discoverability for new contributors becomes "look at the three `register*` methods". The reference for downstream consumers becomes the list of bound contracts in `Contracts/` — every one of those is a swap seam.

### E. Documentation

The convention lands in `packages/lunar/CLAUDE.md` under the existing "Conventions specific to Lunar" section as part of the implementation PR. This is contributor guidance, not consumer guidance — distinct from the outstanding "Add Boost guidelines to packages" TODO, which is for downstream apps installing Lunar.

Bullet text (final wording in the PR, this is the sketch):

> **Service-layer DI**: every service-layer class (`Actions/`, `Managers/`, `Drivers/`, `Generators/`, `Orders/`, `Pricing/`, `Validation/`, `Search/`, `Telemetry/`, `Listeners/`, `Observers/`, `Pipelines/`) declares its collaborators on the constructor with PHP 8 promoted properties. Do not call `app(...)`, `App::make(...)`, `resolve(...)`, or a facade for a collaborator inside `execute()` / `handle()` / `get()` — inject it. Per-call runtime arguments (a cart, a purchasable, a quantity) stay on the method signature. Bind every public service seam to an interface in `Contracts/` from `LunarServiceProvider::register()` via one of the three helper methods (`registerActions()`, `registerManagers()`, `registerServices()`). Consumers swap an implementation by binding the interface in their own service provider — do not introduce `config('lunar.*', SomeClass::class)` substitution keys for class swaps; config is for values, the container is for substitutions.

A one-paragraph rationale follows the bullet, pointing at this spec (`specs/completed/0016-service-layer-di.md` once shipped) for the why and the interface map.

### F. Test impact

- Tests that did `AddOrUpdatePurchasable::run(...)` switch to `app(AddsOrUpdatesPurchasable::class)->execute(...)` or to constructor-injected fakes when the test is asserting *which* implementation runs.
- Tests that overrode actions through `config(['lunar.cart.actions.add_to_cart' => MyAction::class])` switch to `$this->app->bind(AddsOrUpdatesPurchasable::class, MyAction::class)`. This is a one-line swap.
- Tests that mocked a manager by binding its impl class can keep doing so; the interface binding is what `Cart` resolves so they bind the interface instead.

## Alternatives considered

- **Leave the config-string substitution in place and only DI the constructors.** Considered. Rejected because the two seams (config and container) actively shadow each other — a consumer who binds an interface today, expecting standard Laravel behaviour, finds their binding silently ignored at the action call sites in `Cart`. Keeping both for "backwards compatibility" preserves the bug. The Upgrade package documents the substitution so v1.x consumers using config-string overrides have a one-line migration.
- **Convert Actions into invokable single-method classes (`__invoke`).** Considered. The PHP-ecosystem-fashionable shape, and it shortens call sites. Rejected because every existing action has a domain-specific method name (`execute`, `handle`, `generate`, `apply`) that reads as the verb at the call site, and `__invoke` loses that signal. The constructor-injection seam is the same either way; the verb is the only thing the change would affect, and we lose more clarity than we gain.
- **Auto-discover bindings via attributes.** PHP 8 attributes on classes (`#[BindTo(Foo::class)]`) and a service-provider loop. Considered. Rejected: adds a discovery cost and a magic layer for the marginal benefit of not maintaining the binding map. The map is ~30 lines once flat and is the canonical list of swap seams, which has documentation value.
- **Adopt a third-party DI tooling layer (e.g. `tomasvotruba/bladestan`-style autowiring add-ons, or PHP-DI).** Rejected: Laravel's container already does autowiring; the gap is convention, not capability. Adding tooling here would solve a problem we don't have.
- **Do nothing.** The system works. But every new contributor reinvents the resolution pattern (look at the inconsistency in `LunarServiceProvider::register()` for evidence), every downstream consumer has to read the source to find swap seams, and every test fixture has to know which seam the production code currently uses. The cost is paid quietly and continuously; this spec pays it down once.

## Migration impact

- **Database migrations**: none.
- **Public contract surface**:
  - **Net-additive**: ~30 new interfaces in `Contracts/Actions/`, plus `Contracts\CouponValidator`. Each is a new swap seam.
  - **Breaking**: `AbstractAction::make()` and `AbstractAction::run()` static helpers are removed. Any consumer calling `MyAction::run($a, $b)` instead of `app(MyAction::class)->execute($a, $b)` will break. A Rector rule in the `upgrade` package rewrites these one-for-one — `XAction::run(...args)` → `app(XAction::class)->execute(...args)`. The rule covers the four call patterns: `Cls::run(...)`, `Cls::make()->execute(...)`, `new Cls`, and `(new Cls)->execute(...)`.
  - **Breaking (config keys removed)**: `lunar.cart.actions.*` (8 keys), `lunar.cart.fingerprint_generator`, `lunar.discounts.coupon_validator`. A second Rector rule emits a deprecation warning during the v1→v2 upgrade pass if these keys are present in the consumer's `config/lunar/*.php`, with a one-line container-binding equivalent in the warning text. The keys are removed in v2 — the warning is the consumer's pointer to the new pattern.
- **Behavioural change for merchants**: none for the default code path. `PricingManager` reading the user on demand (instead of at construction) fixes the latent bug where a pre-login resolution captured `null`; this is a correctness change, not a regression.
- **Upgrade path for v1.x consumers**: Rector rules above + a short section in the upgrade package notes ("Action overrides: bind the interface, not the config key"). The interface map in this spec doubles as the upgrade reference.
- **Translation / locale impact**: none — no user-facing strings change.
- **Filament / admin impact**: any admin/filament code that called `Action::run(...)` or `(new Action)->execute(...)` switches to `app(Interface::class)->execute(...)`. The `lunar.cart.actions.*` config-string substitution is not used in the admin or filament packages (verified by grep at spec time), so no admin-side overrides need rewriting.

## Open questions

- **Granularity of action interfaces.** The map in §C.1 is one interface per action. An alternative is one interface per *capability group* (e.g. `CartActions` with methods `add`, `remove`, `update`, ...). Rejected provisionally because a fat interface forces a consumer who wants to swap only `add_to_cart` to implement all of `CartActions`. Open to a counter-argument in PR review — the cost is interface count, the benefit is discoverability.
- **`SortProducts` family.** Three classes (`SortProducts`, `SortProductsByPrice`, `SortProductsBySku`) currently coexist. Are they one interface with three bindings (keyed by sort mode), or three separate interfaces? Resolve in the implementation PR — leans toward keyed bindings via `app()->bind(SortsProducts::class.':price', SortProductsByPrice::class)` to keep the seam shape consistent.
- **Pipelines as service-layer.** `Pipelines/Cart/*` and `Pipelines/Order/Creation/*` are resolved by `Illuminate\Pipeline\Pipeline` through the container already (they auto-wire). They are not in §C's migration list. Decision: leave alone unless a specific pipeline pipe is found to use facades for collaborators it could inject (audit in the implementation PR; fix any that come up but don't widen scope).
- **Search package.** `packages/search/src/SearchManager.php` and friends were not audited in detail for this spec (search is its own substantial surface). Decision: apply the same convention there in a follow-up if any drift exists; not blocking this spec.

## Sequencing

This spec lands **after** [[0013-base-directory-reorganisation]] (already drafted), because the action interface namespace in §C.1 (`Contracts/Actions/`) is the post-relocation shape. Lands **after** [[0014-price-calculator]] because the SystemTaxDriver migration in §C.4 depends on the `PriceCalculatorInterface` binding existing.

No ordering constraint relative to other outstanding TODO items.

## Implementation notes

Shipped across nine commits on `feature/0016-service-layer-di`. Deviations from the proposal above, with rationale:

- **Sub-strategy/sub-lookup actions stayed concrete.** `SortProductsByPrice` / `SortProductsBySku` (resolving the §C.1 open question) and `GetTaxZoneCountry` / `GetTaxZonePostcode` / `GetTaxZoneState` did **not** get their own interfaces. They are injected as concretes into the public dispatchers (`SortProducts` → `SortsProducts`, `GetTaxZone` → `GetsTaxZone`), which keeps one swap seam per dispatcher instead of an interface per internal helper. The table in §C.1 over-listed these.
- **`UrlGenerator` is not container-bound as a singleton.** §C.3 suggested a singleton; the generator is stateful (mutates `$this->model` per call), so a shared singleton would be a footgun. It resolves fresh via the existing `app(config('lunar.urls.generator'))` path — the substantive fix was deferring `Language::getDefault()` out of the constructor. The `lunar.urls.generator` config key stays because `null` is a meaningful "disable URL generation" value, not a class swap.
- **`SystemTaxDriver` Blink injection is a fresh per-driver store, not the global `'blink'` singleton.** Aliasing `Blink::class` to the `'blink'` binding created a container resolution cycle (`'blink'`'s concrete is `Blink::class`). `Blink` autowires a fresh instance; because the driver is cached in the singleton `TaxManager`, per-request memoisation is preserved.
- **`CreateCurrencyPrices` keeps `handle()`.** The contract declares `handle()` rather than `execute()` to match the queued-job caller; noted in the CLAUDE.md convention as the one exception.
- **Config-key deprecation Rector rule descoped.** §"Migration impact" floated a second Rector rule warning on removed config keys (`lunar.cart.actions.*`, etc.). A config-array Rector rule is fragile for marginal benefit; the removed keys are covered by this spec's documented migration and the removed-class catalogue instead. The `Action::run()` → `app(Contract::class)->execute()` rule (`RewriteActionRunCallRector`) shipped with a fixture test.
- **Convention enforcement (added in review).** Removing `AbstractAction` left nothing anchoring the action layer, so `tests/core/Unit/ArchitectureTest.php` asserts every `Actions/` class implements a contract (excluding the five deliberately-concrete internal helpers), exposes `execute()`, and imports no Lunar service facades. To make those rules exceptionless: `CreateCurrencyPrices::handle()` was renamed to `execute()` (dropping the one method-name carve-out); `CalculateLine` / `CalculateLineSubtotal` / `RefundOrder` — which still reached for the `Taxes` / `Pricing` / `PriceCalculator` facades — now constructor-inject those collaborators; and `Managers\TaxManager` was made to actually `implement Contracts\TaxManager` (its `buildProvider()` signature realigned) so the contract can be type-hint-injected and the configured singleton is shared.
- **Verification.** Each test suite passes standalone (core 610, filament 40, admin 188, upgrade 29, stripe 40). The full-`pest` run shows 20 pre-existing cross-suite DB-pollution failures (identical count on the base `2.x` branch); this spec adds passing tests and zero new failures. phpstan level 0 clean; pint clean on all changed files.

## References

- Spec [[0013-base-directory-reorganisation]] — establishes the `Contracts/` namespace shape (no `Interface` suffix) this spec uses for action contracts.
- Spec [[0014-price-calculator]] — first example of the convention being codified (interface in `Contracts/`, default in a domain folder, singleton binding in `LunarServiceProvider`). This spec generalises that pattern.
- `Lunar\Core\LunarServiceProvider::register()` — the inconsistent shape this spec normalises.
- `Lunar\Core\Models\Cart` (action dispatch sites) and `config/cart.php` (the `actions` block) — the canonical example of the duplicated-seam problem.
