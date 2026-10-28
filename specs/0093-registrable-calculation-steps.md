# 0093 — Registrable calculation steps

- Status: proposed
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

`Contracts\StepRegistry`, bound as a singleton and exposed through a `Steps` facade:

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

The step lists are removed from the shipped `cart.php`, `pricing.php` and `orders.php` config files. Core no longer needs config to know what runs.

A host config that still defines a list key, such as a published v1 or early-v2 config file, replaces that list's seed, and registrations then apply on top of it. Existing published config keeps working unchanged, and an app that has never published config has nothing to maintain.

### Callers

`Cart::calculate()`, `CalculateLines`, `BuyXGetY`, `PricingManager`, `CreateOrder`, the `Cart` validator methods and `PruneCarts` read `Steps::for(...)->all()` through the injected contract, not `config()`.

### Relationship to modifiers

Modifiers stay. A modifier hooks a fixed, named point (before calculation, after a line's subtotal, after order creation) without knowing what the core steps are called. A step is placed relative to a named core step. An add-on that only needs "after the subtotal" uses a modifier and does not depend on core's step names. One that needs to sit between two particular steps registers a step.

## Alternatives considered

- **Keep config, and give add-ons a helper that edits it.** Formalises what bundles does today. Still append-only unless the helper grows positioning, and the host's config still does not show what runs. Rejected.
- **Laravel's tagged services instead of a registry.** Tags give an unordered set, so positioning would need a priority number on each step. Priority numbers collide between add-ons and are invisible at the call site. Rejected in favour of anchors.
- **Priority numbers on a registry.** The same collision problem. Anchoring to a named step states the actual dependency, for example "after `GetUnitPrice`".
- **Replace every list with modifiers.** Modifiers need a hook point per insertion slot. Validators and price matching would need several new hooks each, and a new slot would need a core release.
- **Do nothing.** Every add-on reinvents config rewriting, and each new one makes the ordering harder to reason about.

## Migration impact

- **Database migrations required:** none.
- **Breaking changes to the public contract surface:** new `Contracts\StepRegistry`, `Facades\Steps`, `Enums\StepList` and `Exceptions\UnknownStepException`. Code that reads the list config keys directly sees the key missing unless the host defines it. Code that sets the keys at runtime, as bundles does today, keeps working, because a defined key replaces the seed, but it should move to the registry.
- **Behaviour changes:** none for a store that has not changed its config. The seeded lists match today's defaults.
- **Upgrade path for v1.x consumers:** published config files keep working. `lunar:upgrade` reports defined list keys as an optional manual action, suggesting a move to registrations in a service provider so future core steps are picked up.
- **Translation / locale impact:** none.
- **Filament / admin impact:** none.

## Open questions

- **Should a defined config key replace the seed, or be ignored once registrations exist?** Proposed: replace, so published config keeps working. The cost is that a host with an old published list does not pick up a step core adds later. Owner: Glenn.
- **A command to show the resolved lists?** For example `php artisan lunar:steps`, so a host can see what runs and in what order once add-ons are installed. Proposed: yes, as a final slice. Owner: Glenn.
- **Should modifiers become thin registrations underneath?** For example, `CartLineModifiers` dispatched as a step in `CartLinePricing`. Proposed: no. Their hook points sit inside steps (`subtotalled` is mid-`CalculateLines`), so they cannot be expressed as list entries without splitting those steps. Revisit if keeping both mechanisms proves confusing.

## References

- [[0092-run-cart-and-order-modifiers]] — the fixed hook points; this spec covers precise positions.
- [[0064-scoped-service-lifetimes]] — the singleton rule for registries read after boot.
- #2732 — product bundles add-on, the first consumer, and the motivating case.
- Prior art: `packages/filament/src/Support/ComponentExtensions/Registry.php`, `Discounts::addType()`, `Payments::extend()`.

## Implementation plan

- [ ] Slice 1 — `StepList`, `StepRegistry`, the `Steps` facade and `UnknownStepException`, seeded with today's defaults. Tests: positioning, idempotent adds, registration order for a shared anchor, a missing anchor throwing, a defined config key replacing the seed.
- [ ] Slice 2 — Move every caller in the table above onto the registry, and remove the lists from the shipped config files. Existing cart, order, pricing and pruning tests pass unchanged.
- [ ] Slice 3 — `lunar:upgrade` manual action for defined list keys, `WHATS-NEW.md` entry, and the docs page on registering steps.
- [ ] Slice 4 — `lunar:steps` command listing the resolved lists, if accepted.
- [ ] Follow-up — Move the product bundles add-on (#2732) onto the registry and off config rewriting.
