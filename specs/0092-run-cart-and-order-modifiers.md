# 0092 — Run cart and order modifiers

- Status: implemented
- Author: Glenn Jacobs
- Created: 2026-10-28
- TODO item: Run cart and order modifiers — `CartLineModifier` and `CartModifier` hooks fire from `$cart->calculate()`, `OrderModifier` hooks from order creation, and the unused line actions go (spec 0092)

## Problem

`CartLineModifier`, `CartModifier` and `OrderModifier` are how an add-on changes cart calculation and order creation from code: it registers an instance with `CartLineModifiers`, `CartModifiers` or `OrderModifiers` in its service provider, and the host app edits no config. The docs present line modifiers as working, "via `CalculateLineSubtotal`". None of the three ever runs.

`$cart->calculate()` runs the `lunar.cart.pipelines.cart` pipeline. Its first step, `Pipelines\Cart\CalculateLines`, prices each line through the `cart_lines` pipeline (`GetUnitPrice`) and sets the line's subtotal itself. Nothing in it, or anywhere else in the calculation, reads `CartLineModifiers` or `CartModifiers`.

The only code that runs a line modifier is `Actions\Carts\CalculateLineSubtotal`, which sends the line through the `subtotalled` hook. It has one caller, `Actions\Carts\CalculateLine`, and nothing calls `CalculateLine` except its own unit test. Both actions have been standalone since v1 removed `CartLineManager` (#777), so line modifiers have not run during cart calculation since then. Nothing dispatches `CartModifier` or `OrderModifier`; the docs warn about both. `Actions\Carts\CreateOrder` runs the `lunar.orders.pipelines.creation` pipeline and never reads `OrderModifiers`.

A test that registers a line modifier implementing all three hooks, then calls `$cart->calculate()`, sees no hook fire and the line subtotal unchanged.

The two actions also price one line on its own, which nothing needs: a cart is calculated as a whole, and callers read its cached totals. Their `$customerGroups` parameter is a second source for groups the cart's context already carries ([[0091-pricing-manager-consumes-storefront-context]]).

## Proposal

Run all three kinds of modifier, cart ones from the cart calculation and order ones from order creation, and delete the two line actions.

### Where each hook fires

| Hook | Fires | Runs from |
| --- | --- | --- |
| `CartModifier::calculating($cart)` | Once, before the `cart` pipeline starts. | `Cart::calculate()` |
| `CartLineModifier::calculating($line)` | Per line, before the `cart_lines` pipeline prices it. | `CalculateLines` |
| `CartLineModifier::subtotalled($line)` | Per line, once its unit price and subtotal are set, before the derived fields. | `CalculateLines` |
| `CartLineModifier::calculated($line)` | Per line, once the `cart` pipeline has finished, so discounts, tax and the line total are final. | `Cart::calculate()` |
| `CartModifier::calculated($cart)` | Once, after every line's `calculated` hook. | `Cart::calculate()` |
| `OrderModifier::creating($cart)` | Once, inside the order creation transaction, before the `creation` pipeline starts. | `CreateOrder` |
| `OrderModifier::created($order)` | Once, after the `creation` pipeline and discount consumption, before the transaction commits. | `CreateOrder` |

`CalculateLines` sets `subTotalDiscounted` and `total` from `subTotal` after `subtotalled` returns, not before. A modifier that changes the subtotal therefore changes what discounts and tax are calculated on, which is what `subtotalled` exists for.

`calculating` runs before `GetUnitPrice`, which always sets the unit price, so a unit price set in `calculating` is overwritten. A modifier that changes the price does it in `subtotalled`, or, once [[0093-registrable-calculation-steps]] lands, as a step after `GetUnitPrice`.

Each hook runs through the registered modifiers in registration order, as a pipeline using the hook name as the method, the same way `ShippingManifest` runs `ShippingModifiers`. With no modifiers registered, calculation does no extra work beyond reading an empty collection.

The order hooks run inside `CreateOrder`'s transaction, so an exception from a modifier rolls the order back with everything else.

The cart hooks run inside the `calculate()` call that holds the cart's per-calculation context, so a modifier that prices something sees the same `$cart->context()` as the line it changes.

### Deletions

- `Actions\Carts\CalculateLine`, `Actions\Carts\CalculateLineSubtotal`, `Contracts\Actions\Carts\CalculatesLine` and `Contracts\Actions\Carts\CalculatesLineSubtotal`.
- Their two entries in `ActionServiceProvider`'s `$actions` map.
- `tests/core/Unit/Actions/Carts/CalculateLineTest.php`. Its tax and pricing assertions move to cart-level tests, where they are not already covered.

### Documentation

The docs pages that describe modifiers are corrected once this lands, in the docs repo: `2.x/extending/carts.mdx` describes the hook points above and drops the warning that `CartModifier` is not dispatched, `2.x/reference/carts.mdx` describes where `CalculateLines` runs the line hooks, and `2.x/extending/orders.mdx` describes the order hooks and drops its warning that `OrderModifier` is not dispatched.

## Alternatives considered

- **Delete the modifiers too, and point add-ons at the pipeline config.** The `cart` and `cart_lines` config lists do work. Rejected: an add-on can only reach them by rewriting the host's config at boot, which appends to the end of a list with no ordering against other add-ons and is invisible to the host. Modifiers are the code-level hook, which is the direction v2 is taking. [[0093-registrable-calculation-steps]] covers the config lists.
- **Keep the line actions and narrow their signature.** Removes the redundant `$customerGroups` parameter but keeps a second, unused way to calculate a line. Rejected.
- **Call `CalculateLineSubtotal` from `CalculateLines`.** Would restore `subtotalled` with the smallest diff, but makes the line subtotal depend on two code paths that each price the line, and still leaves `calculating`, `calculated`, `CartModifier` and `OrderModifier` unwired.
- **Do nothing.** The documented extension point keeps doing nothing.

## Migration impact

- **Database migrations required:** none.
- **Breaking changes to the public contract surface:** the four classes and contracts above are deleted. No first-party code calls them. A consumer that calls or rebinds them calls `$cart->calculate()` instead and reads the line's totals.
- **Behaviour changes:** modifiers that are registered start running. A v1 store that registered a line, cart or order modifier never saw it run, so upgrading turns that code on. `WHATS-NEW.md` says so, and the upgrade guide tells stores to review registered modifiers.
- **Upgrade path for v1.x consumers:** `lunar:upgrade` reports references to the deleted classes as a manual action. The `LunarSetList` renames that map the v1 class names to them (`Lunar\Actions\Carts\CalculateLine`, `Lunar\Actions\Carts\CalculateLineSubtotal`, and the concrete-to-contract entries) are removed. No Rector rule can rewrite a single-line calculation into a cart calculation.
- **Translation / locale impact:** none.
- **Filament / admin impact:** none.

## Decisions

- **`BuyXGetY` does not run the line hooks.** It sends reward lines through the `cart_lines` pipeline on its own to price them for the discount calculation, not to add them to the cart's lines, so running the hooks there would run them twice for a line that is also in the cart.

## References

- [[0091-pricing-manager-consumes-storefront-context]] — moved line pricing onto `$cart->context()` and first flagged the line actions' parameter.
- [[0093-registrable-calculation-steps]] — registering steps at precise positions in the config-driven lists; modifiers stay alongside it for fixed hook points.
- Current surface: `Modifiers/CartLineModifier.php`, `Modifiers/CartModifier.php`, `Modifiers/OrderModifier.php` and their collections, `Pipelines/Cart/CalculateLines.php`, `Models/Cart.php` (`calculate()`), `Actions/Carts/CreateOrder.php`, `Actions/Carts/CalculateLine.php`, `Actions/Carts/CalculateLineSubtotal.php`, `ActionServiceProvider.php`, `upgrade/src/Rector/LunarSetList.php`.
- Prior art: `Manifests/ShippingManifest.php` running `ShippingModifiers`.

## Implementation plan

- [x] Slice 1 — Run the line and cart hooks from `CalculateLines` and `Cart::calculate()` at the points above. Tests: each hook fires once per line or cart in the stated order; a `subtotalled` change to the subtotal reaches discounts, tax and the cart total; `calculated` sees final line totals; modifiers run in registration order.
- [x] Slice 2 — Run the order hooks from `CreateOrder` at the points above. Tests: `creating` sees the cart before the pipeline and `created` sees the finished order; an exception from a modifier rolls the order back.
- [x] Slice 3 — Delete the line actions, their contracts, the `$actions` entries and `CalculateLineTest`, moving its uncovered assertions to cart-level tests.
- [x] Slice 4 — Remove the `LunarSetList` renames, add the manual-action note for references to the deleted classes, and add the `WHATS-NEW.md` entry. The docs pages listed above are corrected in the docs repo.
