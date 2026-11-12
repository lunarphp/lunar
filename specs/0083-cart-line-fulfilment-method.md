# 0083 — Fulfilment choice at checkout: a method per line and a pickup location

- Status: proposed
- Author: Glenn Jacobs
- Created: 2026-09-10
- TODO item: Fulfilment choice at checkout — a cart default and per-line fulfilment method, and a typed pickup location on the cart and order (spec 0083)

## Problem

[[0031-fulfilment-methods]] made the fulfilment flow pluggable: `shipping`, `pickup`, `digital`, or anything registered against `FulfilmentMethodManifest`. Two choices the customer makes at checkout still have no typed home: **how** each line is fulfilled, and **where** a pickup happens.

### How a line is fulfilled

Which method a line lands in is decided after the order is placed, by `EnsureInitialFulfilment` walking the registered methods in priority order and letting each `claim()` the lines it recognises. The only inputs are:

- two capability flags copied onto the order line from the purchasable, `requires_shipping` (`isShippable()`) and `requires_fulfilment` (`requiresFulfilment()`). They say what a product *can* be, not how the customer wants it;
- one cart-wide signal: the chosen shipping option's `pickup` flag, which `CreateShippingLine` writes into the shipping line's `meta` and `Order::isPickup()` reads back.

So:

- **No per-line choice.** The `pickup` flag is all or nothing. A trade customer who wants the bulky item delivered and the small one held at the counter has to place two orders.
- **Intent only travels through a shipping rate.** A digital-only cart has no shipping option and no way to express intent. A registered method (a prescription flow, a 3PL hand-off) can't be chosen by the customer, only inferred from the order afterwards.
- **Nothing to read before the order exists.** A cart line has no accessor saying how it will be fulfilled.

### Where a pickup happens

- `meta['pickup']` has no type. A stored `"false"` counted as a pickup until `isPickup()` started comparing with `=== true`, and every new reader has to remember to.
- Core doesn't record the pickup location. `CreateFulfilment` gives every fulfilment, pickups included, the default location. Checkouts write their own `meta['pickup_point']` onto the shipping line in a shape core doesn't define, and the panel reads it defensively (#2749).
- A `Location` has a name, a handle and meta, but no address, so even the right location can't tell the customer where to go.

Two existing mechanics constrain the fix. Cart-line identity is the purchasable plus a `meta` diff (`GetExistingCartLine`, `CreateOrderLines`, `MergeCart`), so a per-line method must join that identity or lines with different methods merge. Order lines are snapshots ([[0030-fulfillable-order-lines]]), so the resolved method is stamped at order creation, not re-derived from the cart.

Before release this is nullable baseline columns and two contract additions. After release it is a schema migration, a contract break on `FulfilmentMethod` and on the add-to-cart action, a deprecation, and a Rector cycle.

## Proposal

The customer's choice belongs on the cart, and is copied to the order when it is placed. How the goods are handed over belongs on the fulfilment, which already records a `method` and a `location_id`. Displays prefer the fulfilment, and fall back to the order before a fulfilment exists.

Lines with no explicit method keep today's claim-based inference, so a storefront that changes nothing behaves as it does now.

### A. Schema

Baseline edits while v2 is in alpha:

| Table | Column | Means |
|---|---|---|
| `carts` | `fulfilment_method`, nullable string, indexed | the cart-wide default, set explicitly |
| `cart_lines` | `fulfilment_method`, nullable string, indexed | the per-line override; null inherits the cart default |
| `order_lines` | `fulfilment_method`, nullable string, indexed | the method resolved at order creation; null means inferred when fulfilments are created |
| `carts` | `pickup_location_id`, nullable foreign key to `locations` | the pickup location, set explicitly |
| `orders` | `pickup_location_id`, nullable foreign key to `locations` | where the order's pickup lines are collected; null when nothing is picked up |
| `locations` | address columns (section I) | where the location is |

Models gain the matching properties and relations: `Cart::pickupLocation()` and `Order::pickupLocation()` are `BelongsTo`. Factories default every new column to null.

### B. `FulfilmentMethod` contract additions

```php
interface FulfilmentMethod
{
    // ...existing surface unchanged...

    /**
     * Whether this method can fulfil the purchasable. Checked when a method is
     * requested for a cart line, and when the cart default is applied to a line:
     * a default that can't fulfil the line is skipped, not forced.
     */
    public function supports(Purchasable $purchasable): bool;

    /**
     * Whether this method delivers to the customer, so it needs a shipping
     * option and a shipping address, and its lines count towards shipping rates.
     */
    public function requiresDelivery(): bool;
}
```

| Method | `supports()` | `requiresDelivery()` |
|---|---|---|
| `shipping` | `$purchasable->isShippable()` | `true` |
| `pickup` | `$purchasable->isShippable()` | `false` |
| `digital` | `$purchasable->requiresFulfilment() && ! $purchasable->isShippable()` | `false` |

`supports()` is each method's `claim()` rule asked of a single purchasable, before the order exists. `claim()` stays as the fallback (section F).

`FulfilmentMethodManifest` gains `supporting(Purchasable $purchasable): Collection`, the registered methods that support the purchasable, keyed by method key in priority order, for storefront pickers and the validator.

### C. The pickup location on the shipping option

- `ShippingOption` gains `public ?Location $pickupLocation = null`.
- `$pickup` is deprecated. It stays as a constructor argument for now. The option gains `isPickup(): bool`, true when `$pickup` is true or a location is set, and core reads that instead of the property.
- The table-rate `Pickup` driver reads an optional `location_id` from its shipping method's `data` and returns options with that location. The panel and Filament shipping method forms get a location select for the pickup driver.

### D. Resolution

One action, `Actions\Fulfilment\ResolveCartFulfilment` (contract `ResolvesCartFulfilment`, registered in `ActionServiceProvider`), answers both questions. The model accessors delegate to it.

```php
Cart::fulfilmentMethod(): ?FulfilmentMethod
    // 1. the explicit carts.fulfilment_method
    // 2. else from the chosen shipping option: pickup if the option is a pickup, otherwise shipping
    // 3. else null

CartLine::fulfilmentMethod(): ?FulfilmentMethod
    // 1. the explicit cart_lines.fulfilment_method
    // 2. else the cart default, if it supports($line->purchasable)
    // 3. else null: inferred when fulfilments are created

Cart::resolvedPickupLocation(): ?Location
    // null when no line resolves to pickup; otherwise
    // 1. the explicit carts.pickup_location_id
    // 2. else the chosen shipping option's pickupLocation
    // 3. else the default location
```

The cart default is a preference, never a coercion. A cart whose default is `shipping` can hold a licence key: `shipping` doesn't support it, so the line resolves to null and `Digital::claim()` picks it up at order creation as it does today.

The location falls back to the default location, which is what `CreateFulfilment` does for every pickup today. A store with one location sets nothing. A store with several sets the location through a pickup option or `setPickupLocation()`.

An explicit method key that is no longer registered resolves to null at that step and falls through. The cart is still orderable, and the claim pass handles the line.

### E. Cart surface

**Adding a line.** `Cart::add()` and `addLines()` gain an optional trailing argument, and so does the action contract:

```php
$cart->add($variant, quantity: 2, fulfilmentMethod: 'pickup');
$cart->addLines([['purchasable' => $variant, 'quantity' => 2, 'fulfilment_method' => 'pickup']]);

interface AddsOrUpdatesPurchasable
{
    public function execute(Cart $cart, Purchasable $purchasable, int $quantity = 1, array $meta = [], ?string $fulfilmentMethod = null): void;
}
```

**Changing it.** Three model verbs, each delegating to an action contract ([[0029-entry-point-conventions]]):

```php
$cart->setFulfilmentMethod(?string $key, bool $refresh = true): Cart;   // SetsCartFulfilmentMethod
$cartLine->setFulfilmentMethod(?string $key): CartLine;                 // SetsCartLineFulfilmentMethod
$cart->setPickupLocation(?Location $location, bool $refresh = true): Cart; // SetsCartPickupLocation
```

Null clears the value. Each runs a validator chain first, as `setShippingOption()` does:

- `lunar.cart.validators.set_fulfilment_method`, default `Validation\Cart\CartFulfilmentMethod`: the key is null or registered.
- `lunar.cart.validators.set_line_fulfilment_method`, default `Validation\CartLine\CartLineFulfilmentMethod`: the key is null, or registered and `supports($purchasable)`.
- `add_to_cart` gains `CartLineFulfilmentMethod` after `CartLineAvailability`.

Failures throw a `CartException` with `exceptions.carts.fulfilment_method_unknown` or `exceptions.carts.fulfilment_method_unsupported`.

**Identity and fingerprint.** The method joins line identity wherever the `meta` diff is used: `GetExistingCartLine`, the existing-line match in `CreateOrderLines`, and `MergeCart`. The same variant for delivery and for pickup is two lines. `GenerateFingerprint` includes each line's method, the cart's method and the cart's pickup location, so changing them invalidates a checkout fingerprint.

**Delivery lines.** `Cart::deliveryLines(): Collection<int, CartLine>` returns the lines that need delivering. A line is a delivery line when its explicit method (the line override, else the explicit cart default where supported) `requiresDelivery()`, or when it has no explicit method and `isShippable()`. The shipping option is deliberately not consulted: shipping rates are calculated over `deliveryLines()`, so the rate can't decide which lines it covers. `Cart::isShippable()` keeps its name and becomes `deliveryLines()->isNotEmpty()`. Its behaviour only changes when an override is set.

### F. Order side

**Stamp.** `CreateOrderLines` sets `'fulfilment_method' => $cartLine->fulfilmentMethod()?->getKey()`. The whole chain applies here because the shipping option is fixed by now, so a cart with a pickup option stamps `pickup` on its physical lines. The order line says how it was requested before any fulfilment exists.

**Pickup location.** `CreateOrder` sets `orders.pickup_location_id` from `Cart::resolvedPickupLocation()`. `Order::isPickup()` keeps its signature and becomes `pickup_location_id !== null`: the order has lines to be picked up. It may also have lines to deliver.

**Shipping line.** `CreateShippingLine` stops writing `meta['pickup']`. Core never wrote `meta['pickup_point']`; the panel stops reading it.

**Fulfilment creation.** `EnsureInitialFulfilment` gains a first pass ahead of the claim loop:

1. **Explicit pass.** Group the fulfillable lines that have a `fulfilment_method` by key. For each registered key, create one fulfilment in that method's `defaultState()` covering those lines at full quantity, and take them out of the pool. A key that is no longer registered leaves its lines in the pool.
2. **Claim pass.** Today's priority-ordered `claim()` loop over what remains.

A pickup fulfilment is created at `orders.pickup_location_id`; other methods keep the default location. `Pickup::claim()` reads `Order::isPickup()` as the fallback for unstamped lines, from orders created outside the cart pipeline or upgraded from v1. The existing `FulfilmentQuantity` guard still applies, because the explicit pass draws from `fulfillableLines()`.

### G. Checkout validation

`ValidateCartForOrderCreation` re-keys its shipping rules over delivery lines:

- a shipping option is required when the cart has delivery lines;
- a shipping address is required when the cart has delivery lines and the chosen option is not a pickup.

A cart whose physical lines are all set to `pickup` needs neither a shipping option nor a shipping address, a case the current model can't express.

### H. Shipping rates over delivery lines

The table-rate drivers and resolver sum weight, subtotal, stock and exclusions over `$cart->lines`. They switch to `$cart->deliveryLines()`:

- `Drivers\ShippingMethods\{FlatRate,FreeShipping,ShipBy}`: subtotal thresholds and product exclusions;
- `Resolvers\ShippingRateResolver`: `cartWeightKg` and `allCartItemsAreInStock`;
- `Models\ShippingRate`: the per-line loop.

`Drivers\ShippingMethods\Pickup` keeps `$cart->lines`: its exclusion check asks whether anything in the cart can't be picked up. A line picked up from the counter no longer adds weight to a parcel rate, and a free-shipping threshold is met by what is actually shipped.

### I. Location address

`locations` gains nullable address columns matching the order address shape: `line_one`, `line_two`, `line_three`, `city`, `state`, `postcode`, `country_id`, `contact_phone`. `Location` gets the properties and a `country()` relation. The panel and Filament location forms get the fields, so the pickup location can be shown to the customer, in notifications such as `order-ready-for-pickup`, and in the admin.

### J. Delivery to a pickup point

A locker or a carrier's parcel shop is not one of the store's locations, and the goods reach it by carrier with tracking. It stays a `shipping` fulfilment, with the point's address as the shipping address. Points are not modelled as locations.

### K. Admin

- The panel order view shows the pickup fulfilment's location, falling back to the order's pickup location, and replaces the #2749 reads of `meta['pickup_point']`. It shows a shipping address card whenever the order has delivery lines, so a mixed order shows both.
- Filament's order page does the same.
- `order_lines.fulfilment_method` can be shown as a line subline later. Only after staff move lines by hand does it differ from the fulfilment's method.

## Alternatives considered

- **A `meta['fulfilment_method']` convention, no schema.** Untyped, unvalidated, invisible to `EnsureInitialFulfilment`, and squats a key in the consumer's `meta`. A store can already do this with a custom `claim()`, and that it is possible is why it is undiscoverable.
- **Keep the pickup location only on the order, set from the shipping option.** This was the pickup half of the typed-checkout-choices draft (#2846). It breaks on a mixed cart: the cart picks a delivery rate, so no location is recorded and the pickup lines have nowhere to be collected from. The location has to resolve from the cart, with the shipping option as one source.
- **A pickup location per line.** Collecting different lines from different counters is stock routing, and belongs to the location-scoped availability item in the TODO ideas. One location per order covers checkout today, and a per-line column can be added beside `cart_lines.fulfilment_method` later.
- **Per-line shipping options (commercetools "multiple" shipping mode).** Each line targets an address and a rate. Much larger, and the wrong level: the customer's choice is how a line is fulfilled, and the rate stays a price for the cart. Shopify's delivery groups reach the same conclusion.
- **Cart default only, no line override.** Covers the common case, but the default is already derivable from the shipping option. The value is in the override.
- **Let the shipping option own the method.** Two writers for one value, the option and the setter, drifting apart. The option is a derived read instead (section D).
- **Make the cart default apply to every line.** A `shipping` default would stamp `shipping` on a licence key and the order would need a parcel for it.
- **Pickup points as `Location` records.** Locations are where stock is held and fulfilled from. A parcel shop isn't, and stock allocation and location pickers would offer it.

## Migration impact

- **Database** (baseline edits): `fulfilment_method` on `carts`, `cart_lines` and `order_lines`; `pickup_location_id` on `carts` and `orders`; address columns on `locations`. No backfill.
- **Breaking changes:**
  - `Contracts\FulfilmentMethod` gains `supports()` and `requiresDelivery()`. Implementations must add them; Rector note in `LunarSetList`.
  - `AddsOrUpdatesPurchasable::execute()` gains a trailing `?string $fulfilmentMethod = null`. A custom implementation must add it; Rector note.
  - `ShippingOption::$pickup` is deprecated in favour of `$pickupLocation`. Rector can't choose a location, so the upgrade notes show both forms.
  - `meta['pickup']` is no longer written to the shipping line. Code reading it moves to `Order::isPickup()`.
  - `Cart::isShippable()` is based on delivery lines. It only behaves differently when an override is set.
  - Line identity includes the method. Two lines with the same purchasable and `meta` stay separate when their methods differ.
- **New surface** (additive): `Cart::fulfilmentMethod()`, `setFulfilmentMethod()`, `deliveryLines()`, `pickupLocation()`, `resolvedPickupLocation()`, `setPickupLocation()`; `CartLine::fulfilmentMethod()`, `setFulfilmentMethod()`; `Order::pickupLocation()`; `FulfilmentMethodManifest::supporting()`; the actions, contracts, validators and two validator config keys above; the `fulfilmentMethod` argument on `add()` and `addLines()`.
- **Upgrade from v1:** no data step. v1 didn't store `$collect` on the order, so v1 pickup orders can't be identified and keep a null pickup location. Their lines keep inferring through `claim()`. The upgrade guide says so.
- **Translations (16 locales):** the two cart exception messages in `core`; the location address fields and the pickup driver's location select in `panel` and `admin`.
- **Admin:** section K, the location address forms, and the pickup shipping method's location select.

## Open questions

1. **A pickup option with a line overridden to `shipping`.** The override wins for that line: it becomes a delivery line, a shipping address is required, and the pickup rate still prices the cart. The storefront is expected to filter rates against `deliveryLines()`. Should order creation reject the combination instead? Owner: slice 5.
2. **Changing a fulfilment's method.** Core has no action to turn a pickup fulfilment into a shipping one when the customer can't come in. Is it needed for beta, and does it belong here? Owner: Glenn.
3. **Marking a shipping address as a pickup point.** A typed field on the address so the panel can show it is a locker, or out of scope?
4. **Showing the probable inferred method.** When a line resolves to null, a storefront may want to show what `claim()` will probably do. `FulfilmentMethodManifest::supporting()` lists the candidates in priority order. Whether to add an accessor for the first is left to the storefront API spec.

## References

- [[0031-fulfilment-methods]]: the method seam and `claim()`.
- [[0030-fulfillable-order-lines]]: why the method is stamped on the order line.
- [[0045-optional-purchasables-and-shipping-de-morph]]: the shipping line and the `pickup` meta it carries today.
- [[rename-collection-fulfilment-to-pickup]] (#2717): the method's current name.
- [[0029-entry-point-conventions]]: model verbs delegating to action contracts.
- [[0016-service-layer-di]]: actions bound in `ActionServiceProvider`.
- [[payment-terms]]: `OnPickup` terms are validated against the pickup this spec records.
- #2749: the panel's reads of `meta['pickup_point']` and the pickup flag.
- #2846: the typed-checkout-choices draft whose pickup half this spec absorbs.
- `Actions/Carts/GetExistingCartLine.php`, `Actions/Carts/MergeCart.php`, `Pipelines/Order/Creation/CreateOrderLines.php`: where line identity is a purchasable plus a `meta` diff.
- `Pipelines/Order/Creation/CreateShippingLine.php`, `Drivers/FulfilmentMethods/Pickup.php`, `Actions/Fulfilment/CreateFulfilment.php`, `Actions/Fulfilment/EnsureInitialFulfilment.php`.
- Prior art: Shopify cart delivery groups and `FulfillmentOrder.deliveryMethod`; commercetools `Cart.shippingMode = Multiple`; Saleor `Checkout.deliveryMethod` (cart-wide only).

## Implementation plan

- [ ] Slice 1 — `supports()` and `requiresDelivery()` on the contract and the three core drivers, `FulfilmentMethodManifest::supporting()`. Tests.
- [ ] Slice 2 — Location address: columns, model, panel and Filament location forms, translations.
- [ ] Slice 3 — Cart side: the cart and cart-line columns, `ShippingOption::$pickupLocation`, the table-rate `Pickup` driver's location, `ResolveCartFulfilment` and the accessors, the `add()` argument, the three setters, validators and config keys, line identity, fingerprint. Tests.
- [ ] Slice 4 — Order side: the order-line stamp and identity match, `orders.pickup_location_id`, `isPickup()` on the column, `CreateShippingLine` dropping `meta['pickup']`, the explicit pass and pickup fulfilments at the order's location. Tests.
- [ ] Slice 5 — Delivery lines: `deliveryLines()`, `isShippable()`, `ValidateCartForOrderCreation`, table-rate drivers and resolver. Tests in `core` and `table-rate-shipping`. Settles open question 1.
- [ ] Slice 6 — Admin: panel and Filament order views read the typed fields; the #2749 meta reads go.
- [ ] Slice 7 — Exception messages in 16 locales, Rector notes, upgrade guide, docs.
