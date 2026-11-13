# 0083 — Fulfilment choice: a method per line, a pickup location, and changes after placement

- Status: proposed
- Author: Glenn Jacobs
- Created: 2026-09-10
- TODO item: Fulfilment choice at checkout — a cart default and per-line fulfilment method, per-variant allowed methods, a typed pickup location on the cart and order, and changing a fulfilment's method after placement (spec 0083)

## Problem

[[0031-fulfilment-methods]] made the fulfilment flow pluggable: `shipping`, `pickup`, `digital`, or anything registered against `FulfilmentMethodManifest`. Two choices the customer makes at checkout still have no typed home: **how** each line is fulfilled, and **where** a pickup happens.

### How a line is fulfilled

Which method a line lands in is decided after the order is placed, by `EnsureInitialFulfilment` walking the registered methods in priority order and letting each `claim()` the lines it recognises. The only inputs are:

- two capability flags copied onto the order line from the purchasable, `requires_shipping` (`isShippable()`) and `requires_fulfilment` (`requiresFulfilment()`). They say what a product *can* be, not how the customer wants it;
- one cart-wide signal: the chosen shipping option's `pickup` flag, which `CreateShippingLine` writes into the shipping line's `meta` and `Order::isPickup()` reads back.

So:

- **No per-line choice at checkout.** The `pickup` flag is all or nothing. A trade customer who wants the bulky item delivered and the small one held at the counter has to check out twice.
- **No way to restrict an item's methods.** A variant has one `shippable` flag, and `false` means digital. A collection-only item (cement, timber, anything the store won't courier) can't be expressed: marked not shippable, `Digital` claims it; left shippable, it is delivered with everything else. A drop-shipped item that can't be collected has the same problem the other way round. With one collection-only item in the basket, today's all-or-nothing choice forces the whole order to pickup, and a customer who wanted the rest delivered is likely to abandon it.
- **Intent only travels through a shipping rate.** A digital-only cart has no shipping option and no way to express intent. A registered method (a prescription flow, a 3PL hand-off) can't be chosen by the customer, only inferred from the order afterwards.
- **Nothing to read before the order exists.** A cart line has no accessor saying how it will be fulfilled.
- **No change after placement.** `SplitFulfilment` keeps the source's method and location, and `MoveFulfilmentLines` refuses a method mismatch. A customer who paid for delivery and then wants to collect one item can't be accommodated, and neither can a pickup the customer can no longer make.

### Where a pickup happens

- `meta['pickup']` has no type. A stored `"false"` counted as a pickup until `isPickup()` started comparing with `=== true`, and every new reader has to remember to.
- Core doesn't record the pickup location. `CreateFulfilment` gives every fulfilment, pickups included, the default location. Checkouts write their own `meta['pickup_point']` onto the shipping line in a shape core doesn't define, and the panel reads it defensively (#2749).
- A `Location` has a name, a handle and meta, but no address, so even the right location can't tell the customer where to go.

Two existing mechanics constrain the fix. Cart-line identity is the purchasable plus a `meta` diff (`GetExistingCartLine`, `CreateOrderLines`, `MergeCart`), so a per-line method must join that identity or lines with different methods merge. Order lines are snapshots ([[0030-fulfillable-order-lines]]), so the resolved method is stamped at order creation, not re-derived from the cart.

While v2 is in alpha this is nullable baseline columns and contract changes made in place. After release it would be a schema migration and a contract break on `FulfilmentMethod` and on the add-to-cart action.

## Proposal

The customer's choice belongs on the cart, and is copied to the order when it is placed. How the goods are handed over belongs on the fulfilment, which already records a `method` and a `location_id`. Displays prefer the fulfilment, and fall back to the order before a fulfilment exists.

Lines with no explicit method keep today's claim-based inference, so a storefront that changes nothing behaves as it does now.

Every value core reads, writes or routes on is a typed column, cast or relation. Core reads and writes none of its own keys in `meta`: `meta` belongs to the store and its add-ons. Core may carry a store's `meta` through without interpreting it, as it does when copying cart-line `meta` to the order line and comparing it for line identity. This spec removes the two places the pickup choice currently lives in `meta`.

Sections A to K cover what the customer chooses before the order is placed. Section L covers what staff can change afterwards. The two are separate: the customer's choice is recorded on the order as requested, and later changes act on fulfilments.

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
| `product_variants` | `fulfilment_methods`, nullable JSON list of method keys, cast to an array | the methods this variant may use; null allows any method that supports it |
| `shipping_methods` (table-rate) | `pickup_location_id`, nullable foreign key to `locations` | the location a pickup method's options collect from (section C) |

Models gain the matching properties and relations: `Cart::pickupLocation()` and `Order::pickupLocation()` are `BelongsTo`. Factories default every new column to null, so existing products allow every method.

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

`Purchasable` gains `allowedFulfilmentMethods(): ?array`, the method keys the purchasable may use, or null for no restriction. `ProductVariant` reads `product_variants.fulfilment_methods`. A `RestrictsFulfilmentMethods` trait returns null for other purchasables, so a custom purchasable adds the trait or the method.

`FulfilmentMethodManifest` gains `supporting(Purchasable $purchasable): Collection`: the registered methods that support the purchasable and that it allows, keyed by method key in priority order. The allow-list is applied here, once, rather than in every driver's `supports()`. Storefront pickers, the validator and resolution all go through it.

A collection-only variant sets `['pickup']`. A drop-shipped variant sets `['shipping']`. The panel and Filament variant forms get a multi-select of the registered methods, empty meaning any.

### C. The pickup location on the shipping option

- `ShippingOption` gains `public ?Location $pickupLocation = null`.
- `$pickup` is removed. An option is a pickup when it has a location: `isPickup(): bool` returns `$pickupLocation !== null`, and core reads that everywhere it read the flag (`CreateShippingLine`, `ValidateCartForOrderCreation`).
- `shipping_methods` gains `pickup_location_id`, a nullable foreign key to `locations`, in the table-rate baseline migration. The table-rate `Pickup` driver returns options with that location, or the default location when the method has none, so every pickup option it returns has a location. It is a typed column rather than a key in the method's `data` JSON, so a deleted location is caught by the foreign key and the value is never read untyped. The panel and Filament shipping method forms get a location select for the pickup driver.

### D. Resolution

One action, `Actions\Fulfilment\ResolveCartFulfilment` (contract `ResolvesCartFulfilment`, registered in `ActionServiceProvider`), answers both questions. It returns a `ValueObjects\Cart\CartFulfilment` with the cart default, each line's method and the pickup location. The model accessors delegate to it. `execute(Cart $cart, bool $withShippingOption = true)`: without the shipping option, only explicit choices and restricted items count, which is what `deliveryLines()` and the pickup-option check in section G use.

```php
Cart::fulfilmentMethod(): ?FulfilmentMethod
    // 1. the explicit carts.fulfilment_method
    // 2. else from the chosen shipping option: pickup if the option is a pickup, otherwise shipping
    // 3. else null

CartLine::fulfilmentMethod(): ?FulfilmentMethod
    // 1. the explicit cart_lines.fulfilment_method
    // 2. else the cart default, if supporting($line->purchasable) includes it
    // 3. else, if the purchasable restricts its methods, the first of supporting($line->purchasable)
    // 4. else null: inferred when fulfilments are created

Cart::resolvedPickupLocation(): ?Location
    // null when no line resolves to pickup; otherwise
    // 1. the explicit carts.pickup_location_id
    // 2. else the chosen shipping option's pickupLocation
    // 3. else the default location
```

The cart default is a preference, never a coercion. A cart whose default is `shipping` can hold a licence key: `shipping` doesn't support it, so the line resolves to null and `Digital::claim()` picks it up at order creation as it does today.

Step 3 is what keeps a restricted item on an allowed method. A collection-only bag of cement in a cart whose default is `shipping` resolves to `pickup`, so the rest of the cart is delivered and the cement is collected. Without the step it would resolve to null, and `Shipping::claim()` would ship it. Step 3 only applies to restricted purchasables: for an unrestricted physical item, the first supporting method in priority order is `pickup` (priority 20, before `shipping` at 30), which would be the wrong default.

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

- `StepList::SetFulfilmentMethodValidation`, default `Validation\Cart\CartFulfilmentMethod`: the key is null or registered.
- `StepList::SetLineFulfilmentMethodValidation`, default `Validation\CartLine\CartLineFulfilmentMethod`: the key is null, or in `supporting($purchasable)`, which covers both the driver's `supports()` and the purchasable's allowed methods.
- `StepList::AddToCartValidation` gains `CartLineFulfilmentMethod` after `CartLineAvailability`.

Validators register through `StepList` since [[0093-registrable-calculation-steps]], so these are step lists rather than config keys.

Failures throw a `CartException` with `exceptions.carts.fulfilment_method_unknown` or `exceptions.carts.fulfilment_method_unsupported`.

**Identity and fingerprint.** The method joins line identity wherever the `meta` diff is used: `GetExistingCartLine` (whose contract gains the same trailing `?string $fulfilmentMethod = null`), the existing-line match in `CreateOrderLines`, and `MergeCart`. The same variant for delivery and for pickup is two lines. `GenerateFingerprint` includes each line's method, the cart's method and the cart's pickup location, so changing them invalidates a checkout fingerprint.

**Delivery lines.** `Cart::deliveryLines(): Collection<int, CartLine>` returns the lines that need delivering. A line is a delivery line when its explicit method (the line override, else the explicit cart default where supported, else the restricted item's own method from step 3) `requiresDelivery()`, or when it has no explicit method and `isShippable()`. A collection-only item is therefore never a delivery line: it adds no weight to a parcel rate and doesn't on its own require a shipping address. The shipping option is deliberately not consulted: shipping rates are calculated over `deliveryLines()`, so the rate can't decide which lines it covers. `Cart::isShippable()` keeps its name and becomes `deliveryLines()->isNotEmpty()`. Its behaviour only changes when an override is set.

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
- a shipping address is required when the cart has delivery lines and the chosen option is not a pickup;
- a pickup option is rejected when a line is explicitly set to a method that delivers, with `exceptions.carts.delivery_option_missing` (open question 1).

[[payment-terms]] validation reads the resolved methods: `OnPickup` needs a line that resolves to `pickup`, and `OnDelivery` a line that resolves to a method that delivers.

A cart whose physical lines are all set to `pickup` needs neither a shipping option nor a shipping address, a case the current model can't express.

**Storefront guidance (docs).** Offer the per-line choice on the cart page, before shipping rates are shown, and mark restricted items ("collection only") where they are listed. The customer then sees "collect this one, deliver the rest" before checkout rather than finding a dead end at the rates step.

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
- `order_lines.fulfilment_method` can be shown as a line subline later. It differs from the fulfilment's method only after staff change it (section L).
- The panel and Filament variant forms get the allowed-methods multi-select (section B).

### L. Changing a fulfilment's method after placement

Once the order is placed, the customer's choice is a record of what was requested. Staff change how the goods are handed over by changing fulfilments.

`Actions\Fulfilment\ChangeFulfilmentMethod` (contract `ChangesFulfilmentMethod`) moves some or all of a fulfilment's outstanding quantities into a new fulfilment with a different method and location:

```php
public function execute(Fulfilment $fulfilment, string $method, ?Location $location = null, ?array $moves = null): Fulfilment;
```

- Only a fulfilment in its method's `defaultState()` or another pre-handover state can change. A shipped or picked-up fulfilment can't.
- `$moves` is `[order_line_id => quantity]`, as for `SplitFulfilment`. Null moves everything, and the source is deleted if it ends up empty.
- The target method must be in `supporting()` for every moved line's purchasable, so a collection-only item can't be changed to shipping.
- The new fulfilment starts in the target method's `defaultState()`.

The two common directions aren't symmetric:

- **Shipping to pickup** is always possible. It needs a location, defaulting to the order's pickup location and then the default location. Whether to refund part of the shipping charge is the staff's decision, through the existing refund composer.
- **Pickup to shipping** needs a shipping address. The action fails with `exceptions.fulfilment_change_address_missing` unless the order has one; the panel lets staff enter it in the same step. Core never charges for the new delivery. Staff take any extra payment separately, for example with `recordPayment()` from [[payment-terms]].

Customers can't make these changes themselves; an account area that wants to offer them calls the action with its own rules.

`order_lines.fulfilment_method` and `orders.pickup_location_id` keep what the customer requested. `Order::isPickup()` reads the fulfilments when there are any (true when any fulfilment uses `pickup`), and falls back to `pickup_location_id` before fulfilments exist. Without this, an order changed from shipping to pickup would still report false.

The panel adds "Change to pickup" and "Change to shipping" to unshipped fulfilment cards, with a location select and, for shipping, the address form. Filament's order page gets the same actions.

## Alternatives considered

- **A `meta['fulfilment_method']` convention, no schema.** Core doesn't use `meta` for its own values. It would also be unvalidated and invisible to `EnsureInitialFulfilment`.
- **The pickup location in the shipping method's `data` JSON.** Untyped, with no foreign key to catch a deleted location. A typed column costs one nullable field.
- **Keep the pickup location only on the order, set from the shipping option.** This was the pickup half of the typed-checkout-choices draft (#2846). It breaks on a mixed cart: the cart picks a delivery rate, so no location is recorded and the pickup lines have nowhere to be collected from. The location has to resolve from the cart, with the shipping option as one source.
- **A pickup location per line.** Collecting different lines from different counters is stock routing, and belongs to the location-scoped availability item in the TODO ideas. One location per order covers checkout today, and a per-line column can be added beside `cart_lines.fulfilment_method` later.
- **Per-line shipping options (commercetools "multiple" shipping mode).** Each line targets an address and a rate. Much larger, and the wrong level: the customer's choice is how a line is fulfilled, and the rate stays a price for the cart. Shopify's delivery groups reach the same conclusion.
- **Cart default only, no line override.** Covers the common case, but the default is already derivable from the shipping option. The value is in the override.
- **Let the shipping option own the method.** Two writers for one value, the option and the setter, drifting apart. The option is a derived read instead (section D).
- **Two flags on the variant, `deliverable` and `collectable`.** Simpler to read, but only covers the two built-in physical methods. An allow-list of method keys also covers methods a store registers, such as a 3PL hand-off.
- **Restrict methods per location instead of per variant.** "Not collectable from this store" is stock routing, which belongs to the location-scoped availability item. "Never collectable" is a property of the product and belongs on the variant.
- **Let customers change the method after placement.** Pickup to shipping can need an address and a payment the order doesn't have. Staff make the change, and a store can expose it to customers with its own rules (section L).
- **Make the cart default apply to every line.** A `shipping` default would stamp `shipping` on a licence key and the order would need a parcel for it.
- **Pickup points as `Location` records.** Locations are where stock is held and fulfilled from. A parcel shop isn't, and stock allocation and location pickers would offer it.

## Migration impact

- **Database** (baseline edits): `fulfilment_method` on `carts`, `cart_lines` and `order_lines`; `pickup_location_id` on `carts` and `orders`; address columns on `locations`; `fulfilment_methods` on `product_variants`; `pickup_location_id` on the table-rate `shipping_methods`. No backfill.
- **Changes to v2 surface** (made in place while v2 is in alpha, with no deprecations):
  - `Contracts\FulfilmentMethod` gains `supports()` and `requiresDelivery()`.
  - `AddsOrUpdatesPurchasable::execute()` gains a trailing `?string $fulfilmentMethod = null`.
  - `Contracts\Purchasable` gains `allowedFulfilmentMethods()`. A custom purchasable adds the `RestrictsFulfilmentMethods` trait or the method.
  - `ShippingOption::$pickup` is removed in favour of `$pickupLocation`.
  - `meta['pickup']` is no longer written to the shipping line. Code reading it moves to `Order::isPickup()`.
  - `Cart::isShippable()` is based on delivery lines. It only behaves differently when an override or a restricted item is in the cart.
  - `Order::isPickup()` reads fulfilments when the order has them.
  - Line identity includes the method. Two lines with the same purchasable and `meta` stay separate when their methods differ.
- **New surface** (additive): `Cart::fulfilmentMethod()`, `setFulfilmentMethod()`, `deliveryLines()`, `pickupLocation()`, `resolvedPickupLocation()`, `setPickupLocation()`; `CartLine::fulfilmentMethod()`, `setFulfilmentMethod()`; `Order::pickupLocation()`; `FulfilmentMethodManifest::supporting()`; `ChangeFulfilmentMethod` and its contract; the `RestrictsFulfilmentMethods` trait; the actions, contracts, validators and two validator config keys above; the `fulfilmentMethod` argument on `add()` and `addLines()`.
- **Upgrade from v1:** no data step. v1 didn't store `$collect` on the order, so v1 pickup orders can't be identified and keep a null pickup location. Their lines keep inferring through `claim()`. `RewriteShippingOptionCollectRector` (replacing `RenameShippingOptionCollectArgRector`) rewrites `collect: true` to `pickupLocation: Location::getDefault()`, where v1 pickups were fulfilled from, and `$option->collect` to `isPickup()`. The upgrade guide says a store with several locations passes its own, and that a v1 custom purchasable adds `allowedFulfilmentMethods()` or the trait.
- **Translations (16 locales):** the two cart exception messages and `fulfilment_change_address_missing` in `core`; the location address fields, the pickup driver's location select, the variant allowed-methods field and the change-method actions in `panel` and `admin`.
- **Admin:** sections K and L, the location address forms, the variant allowed-methods field, and the pickup shipping method's location select.

## Open questions

1. **A pickup option with a line overridden to `shipping`.** Settled in slice 5: order creation rejects it. The pickup rate prices no delivery and the address rule skips pickup options, so allowing it would deliver the line unpriced and unaddressed. The storefront still filters rates against `deliveryLines()`.
2. **Marking a shipping address as a pickup point.** A typed field on the address so the panel can show it is a locker, or out of scope?
3. **Showing the probable inferred method.** When a line resolves to null, a storefront may want to show what `claim()` will probably do. `FulfilmentMethodManifest::supporting()` lists the candidates in priority order. Whether to add an accessor for the first is left to the storefront API spec.

## References

- [[0031-fulfilment-methods]]: the method seam and `claim()`.
- [[0030-fulfillable-order-lines]]: why the method is stamped on the order line.
- [[0069-panel-order-screen-improvements]]: item 6, shipping-refund tracking, which a shipping-to-pickup change may use.
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

- [x] Slice 1 — `supports()` and `requiresDelivery()` on the contract and the three core drivers, `Purchasable::allowedFulfilmentMethods()` and the trait, `product_variants.fulfilment_methods`, `FulfilmentMethodManifest::supporting()` applying the allow-list. Tests.
- [x] Slice 2 — Location address: columns, model, panel and Filament location forms, translations.
- [x] Slice 3 — Cart side: the cart and cart-line columns, `ShippingOption::$pickupLocation`, the table-rate `Pickup` driver's location, `ResolveCartFulfilment` and the accessors including the restricted-item step, the `add()` argument, the three setters, validators and config keys, line identity, fingerprint. Tests.
- [x] Slice 4 — Order side: the order-line stamp and identity match, `orders.pickup_location_id`, `isPickup()` on the column, `CreateShippingLine` dropping `meta['pickup']`, the explicit pass and pickup fulfilments at the order's location. Tests.
- [x] Slice 5 — Delivery lines: `deliveryLines()` including restricted items, `isShippable()`, `ValidateCartForOrderCreation`, table-rate drivers and resolver. Tests in `core` and `table-rate-shipping`. Settles open question 1.
- [x] Slice 6 — Changing a fulfilment's method: `ChangeFulfilmentMethod` and its contract, `isPickup()` reading fulfilments. Tests.
- [x] Slice 7 — Admin: panel and Filament order views read the typed fields and the #2749 meta reads go; the variant allowed-methods field; the change-method actions on fulfilment cards.
- [x] Slice 8 — Exception messages in 16 locales, upgrade guide, docs including the storefront guidance.
