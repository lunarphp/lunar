# 0093 — Registrable calculation steps

- Status: implemented
- Author: Glenn Jacobs
- Created: 2026-10-28
- TODO item: Registrable calculation steps — add-ons register cart, pricing, order-creation and validator steps from code, with an explicit position, instead of rewriting config (spec 0093)

## Problem

Core runs several ordered lists of classes, each read from config:

| List | Config key | Read by |
| --- | --- | --- |
| Cart calculation | `lunar.cart.pipelines.cart` | `Cart::calculate()` |
| Cart line pricing | `lunar.cart.pipelines.cart_lines` | `Pipelines\Cart\CalculateLines`, `DiscountTypes\BuyXGetY` |
| Price matching | `lunar.pricing.pipelines` | `Managers\PricingManager` |
| Order creation | `lunar.orders.pipelines.creation` | `Actions\Carts\CreateOrder` |
| Cart validators | `lunar.cart.validators.{add_to_cart, update_cart_line, remove_from_cart, add_address, set_shipping_option, order_create}` | `Cart` |
| Cart pruning | `lunar.cart.prune_tables.pipelines` | `Console\Commands\PruneCarts` |

These lists are the only way to add a step at a precise point, such as after `GetUnitPrice` or before `CleanUpOrderLines`. For a host app that is a config edit. An add-on has no way in except rewriting the host's config at boot. The product bundles add-on (#2732) does this for four lists:

```php
$append('lunar.cart.validators.add_to_cart', BundleSelection::class);
$append('lunar.cart.validators.update_cart_line', BundleSelection::class);
$append('lunar.cart.pipelines.cart_lines', PriceBundleSelection::class);
$append('lunar.orders.pipelines.creation', CreateBundleComponentLines::class);
```

That approach has three faults:

- **Position.** It can only append. `PriceBundleSelection` has to run after `GetUnitPrice`, and is correct only because `GetUnitPrice` happens to be last.
- **Ordering between add-ons.** Two add-ons appending to the same list run in service provider order, which neither controls.
- **Visibility.** The host's config file no longer describes what runs, and a host that wants to remove or reorder an add-on's step has to know it was injected.

Modifiers ([[0092-run-cart-and-order-modifiers]]) give add-ons fixed hook points around cart calculation and order creation. They cannot place a step between two core steps, and validators, price matching and pruning have no modifier at all.

A general aim of v2 is to need less config and to make add-on development easier. Lists that only change through config work against both.

## Proposal

Each list becomes a registry seeded in code. Add-ons and host apps change it from a service provider, with an explicit position. Core reads the resolved list from the registry instead of from config.

### Naming the lists

A backed enum names every list, so a typo is a type error rather than a silently ignored key:

```php
enum StepList: string
{
    case CartCalculation = 'cart.calculation';
    case CartLinePricing = 'cart.line_pricing';
    case PriceMatching = 'pricing.matching';
    case OrderCreation = 'orders.creation';
    case AddToCartValidation = 'cart.validation.add_to_cart';
    case UpdateCartLineValidation = 'cart.validation.update_cart_line';
    case RemoveFromCartValidation = 'cart.validation.remove_from_cart';
    case AddAddressValidation = 'cart.validation.add_address';
    case SetShippingOptionValidation = 'cart.validation.set_shipping_option';
    case OrderCreateValidation = 'cart.validation.order_create';
    case CartPruning = 'cart.pruning';
}
```

### Registering

`Contracts\StepManifest` (implemented by `Manifests\StepManifest`, following core's other registries such as `CarrierManifest`), bound as a singleton and exposed through a `Steps` facade. `for()` returns the list's `Manifests\StepSequence`:

```php
use Lunar\Core\Enums\StepList;
use Lunar\Core\Facades\Steps;

Steps::for(StepList::CartLinePricing)->after(GetUnitPrice::class, PriceBundleSelection::class);
Steps::for(StepList::OrderCreation)->before(CleanUpOrderLines::class, CreateBundleComponentLines::class);
Steps::for(StepList::AddToCartValidation)->append(BundleSelection::class);

// host app
Steps::for(StepList::CartLinePricing)->remove(PriceBundleSelection::class);
Steps::for(StepList::CartCalculation)->replace(ApplyShipping::class, App\Pipelines\ApplyShipping::class);
```

Each list exposes `append()`, `prepend()`, `before($anchor, $step)`, `after($anchor, $step)`, `remove($step)`, `replace($existing, $step)` and `all()`, which returns the resolved, ordered class list.

- Core seeds each list with today's defaults, in today's order.
- Adding a class that is already in the list does nothing, so a service provider that boots twice cannot run a step twice.
- Several steps placed `after()` the same anchor keep their registration order, each going after those placed before it.
- `before()` or `after()` with an anchor that is not in the list throws `UnknownStepException` when the list is resolved, naming both classes. An add-on positioned against a step the host removed fails loudly in development rather than running in the wrong place.

### Lifetime

Registration happens in service provider `register()` or `boot()`. After boot the registry is only read, so it is a singleton, per the long-lived worker rules in [[0064-scoped-service-lifetimes]]. Changing a list during a request is unsupported. `all()` is computed once per list and cached until the list next changes.

### Config

The step lists are removed from config entirely: `lunar.cart.pipelines`, `lunar.cart.validators`, `lunar.cart.prune_tables.pipelines`, `lunar.pricing.pipelines` and `lunar.orders.pipelines`. The registry is the only source of what runs, and there is no config fallback. A host changes a list the same way an add-on does, from a service provider, and keeps picking up steps core adds later.

The other settings in those files stay: `auth_policy`, `eager_load`, the pruning `enabled` flag and interval, the pricing formatter and tax setting, and the order reference format and generator.

A removed key that is still defined would be ignored, so a custom step in it would stop running without an error. To prevent that, `StepManifest` checks for the removed keys the first time any list is read, and throws `RemovedStepConfigException` if one is defined. The message names the key and the `Steps` call that replaces it. The check reads config only, once per process, so it adds no query.

The check runs on first read rather than at boot so that artisan keeps working with a stale config. A boot check would also stop `php artisan lunar:upgrade`, the command that reports how to fix it. Nothing runs a list before the check, so no step runs from the wrong list.

### Callers

`Cart::calculate()`, `CalculateLines`, `BuyXGetY`, `PricingManager`, `CreateOrder`, the `Cart` validator methods and `PruneCarts` read `Steps::for(...)->all()` through the injected contract, not `config()`.

### Relationship to modifiers

Modifiers stay. A modifier hooks a fixed, named point (before calculation, after a line's subtotal, after order creation) without knowing what the core steps are called. A step is placed relative to a named core step. An add-on that only needs "after the subtotal" uses a modifier and does not depend on core's step names. One that needs to sit between two particular steps registers a step.

## Alternatives considered

- **Keep config, and give add-ons a helper that edits it.** Formalises what bundles does today. Still append-only unless the helper grows positioning, and the host's config still does not show what runs. Rejected.
- **Laravel's tagged services instead of a registry.** Tags give an unordered set, so positioning would need a priority number on each step. Priority numbers collide between add-ons and are invisible at the call site. Rejected in favour of anchors.
- **Priority numbers on a registry.** The same collision problem. Anchoring to a named step states the actual dependency, for example "after `GetUnitPrice`".
- **Replace every list with modifiers.** Modifiers need a hook point per insertion slot. Validators and price matching would need several new hooks each, and a new slot would need a core release.
- **Keep config as a fallback, where a defined list replaces the seed.** Published config would keep working unchanged. Rejected: what runs would depend on whether a key is defined, a host with an old published list would not pick up steps core adds later, and add-on steps would be anchored against a stale list. v2 is a major release, so the break is taken once, with the upgrade step and the config check to carry it.
- **Ignore removed keys silently.** Simplest, but a custom pricing or validation step in a published list would stop running without an error. Rejected in favour of the config check.
- **Do nothing.** Every add-on reinvents config rewriting, and each new one makes the ordering harder to reason about.

## Migration impact

- **Database migrations required:** none.
- **Breaking changes to the public contract surface:** the step-list config keys above are removed. New `Contracts\StepManifest`, `Manifests\StepSequence`, `Facades\Steps`, `Enums\StepList`, `Exceptions\UnknownStepException` and `Exceptions\RemovedStepConfigException`. Code that reads the keys finds them missing. Code that sets them at runtime, as bundles does today, triggers the config check and must move to the registry.
- **Behaviour changes:** none for a store that has not published or changed these lists. The seeded lists match today's defaults. A store that still defines a removed key fails the first time a list is read, until the key is removed.
- **Upgrade path for v1.x consumers:** v1 shipped `pipelines` and `validators` in its config, so most apps that published config define the removed keys. `lunar:upgrade` reads the published `cart.php`, `pricing.php` and `orders.php`:
  - A list that matches v1's defaults is reported as safe to delete.
  - A customised list is reported as a manual action, with the equivalent `Steps::` calls (adds, removals and replacements against the seeded list) printed to paste into a service provider.

  A store that skips the upgrade step hits the config check, whose message gives the same guidance.
- **Translation / locale impact:** none.
- **Filament / admin impact:** none.

## Decisions

- **No config fallback.** The step lists are removed from config outright. The config check and `lunar:upgrade` carry published lists across.
- **`lunar:steps` ships.** The command lists each resolved list in order, so a host can see what runs once add-ons are installed.
- **Modifiers stay a separate mechanism.** Their hook points sit inside steps (`subtotalled` is mid-`CalculateLines`), so they cannot be list entries without splitting those steps. Revisit if keeping both proves confusing.

## References

- [[0092-run-cart-and-order-modifiers]] — the fixed hook points; this spec covers precise positions.
- [[0064-scoped-service-lifetimes]] — the singleton rule for registries read after boot.
- #2732 — product bundles add-on, the first consumer, and the motivating case.
- Prior art: `packages/filament/src/Support/ComponentExtensions/Registry.php`, `Discounts::addType()`, `Payments::extend()`.

## Implementation plan

- [x] Slice 1 — `StepList`, `StepManifest` and `StepSequence`, the `Steps` facade and `UnknownStepException`, seeded with today's defaults. Tests: positioning, idempotent adds, registration order for a shared anchor, a missing anchor throwing.
- [x] Slice 2 — Move every caller in the table above onto the registry, remove the lists from the shipped config files, and add the config check. Existing cart, order and pruning tests pass unchanged; pricing tests that set the config key register through `Steps` instead. Tests: each removed key, when defined, throws `RemovedStepConfigException` naming it.
- [x] Slice 3 — `lunar:upgrade` reporting (`StepListConfigStep`) for published list keys: safe to delete when they match v1's defaults, otherwise the equivalent `Steps::` calls, and the `WHATS-NEW.md` entry. The docs page on registering steps is outside this repo and still to write.
- [x] Slice 4 — `lunar:steps` command listing the resolved lists.
- [ ] Follow-up — Move the product bundles add-on (#2732) onto the registry and off config rewriting.
