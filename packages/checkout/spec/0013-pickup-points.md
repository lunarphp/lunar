# 0013: Pickup points, fulfilment mode, and the branch on the order

- Status: accepted (2026-09-07)
- Author: Alec Ritson
- Created: 2026-09-07
- Depends on: [[0010-cart-session-reconciliation]] (fingerprint, pay boundary),
  [[0011-address-lookup-and-element-bag]] §F (`OrderPlacing`), [[0012-express-payments]] §G
  (fulfilment interplay on the squeeze page)
- Sibling change outside this package: `panel` (order screen). Core and
  `table-rate-shipping` are untouched.

## Problem

Click and collect works end to end in Lunar core and stops one step short in this package.

**What core already does.** A collect order is any order whose shipping line carries
`meta.collect = true`. The `collection` shipping driver returns a `ShippingOption` with
`collect: true` and a `meta` array; `Pipelines\Order\Creation\CreateShippingLine` merges that
meta (plus the `collect` flag) onto the shipping order line; `FulfilmentMethods\Collection`
claims the order's physical lines by reading exactly that array and runs
Pending → Ready for collection → Collected; `ValidateCartForOrderCreation` waives the
shipping-address requirement for a collect option. `FillOrderFromCart` also copies `cart.meta`
onto `orders.meta`, and every shipping driver receives the cart in its `ShippingOptionRequest`.
None of this knows *where* the customer collects from, and none of it needs to: with no pickup
locations defined, an order simply is a collection order.

**What this package gets wrong today.**

1. **Fulfilment mode is client-only.** `useCheckout.js` initialises `state.fulfilment` to
   `'delivery'` and never hydrates it. Reloading a checkout whose cart holds the collect
   option renders delivery mode with the collect option silently active, which is the exact
   condition the parity test plan says must never happen.
2. **A guest cannot choose collect before entering an address.** `Cart::setShippingOption()`
   runs `ShippingOptionValidator`, which fails with "No shipping address on cart" when no
   shipping address row exists, and `SetShippingOption` writes the identifier onto that row.
   `setFulfilment('collect')` posts the collect option immediately, so for a fresh guest the
   toggle 422s. It only works today when a saved address was auto-applied first.
3. **There is no pickup location concept.** The collect panel in `LunarCheckout.vue` is a
   read-only sentence. `ExpressConfirm.vue`'s "Collect from" row shows the shipping method's
   name and its Change link deep-links to a picker that does not exist. `ShippingOption::$meta`
   is dropped by `LunarCheckoutDriver::getShippingOptions()` and never reaches the client.
4. **The panel cannot tell a collect order apart** beyond the fulfilment card's method badge.
   `OrderShowController::shippingOption()` reads the breakdown item, which carries neither
   `collect` nor meta.

## Proposal

Keep Lunar's model: the shipping option is the fact, the shipping line meta is where its
qualifiers land, and the cart is the carrier. The package adds three small things: a server-held
fulfilment mode, a host-provided list of pickup points, and the plumbing that gets the chosen
point into the option meta so core stamps it onto the order without anyone writing to the order.

### A. The pickup point contract

```php
namespace Lunar\Checkout\Contracts;

interface PickupPointProvider
{
    /** @return \Illuminate\Support\Collection<int, \Lunar\Checkout\DataTypes\PickupPoint> */
    public function pointsFor(\Lunar\Core\Models\Cart $cart): \Illuminate\Support\Collection;
}
```

```php
namespace Lunar\Checkout\DataTypes;

final readonly class PickupPoint
{
    /** @param list<string> $lines  @param array<string, mixed> $meta */
    public function __construct(
        public string $handle,
        public string $name,
        public array $lines = [],
        public array $meta = [],
    ) {}

    /** @return array{handle: string, name: string, lines: list<string>, meta: array<string, mixed>} */
    public function toArray(): array;
}
```

The host binds the contract in its own service provider. The package never binds a default.
Resolution is `app()->bound(PickupPointProvider::class)`; unbound means "no points", and
so does an empty collection. The provider receives the cart so a host can filter by basket
(stock, oversized, channel) later without a contract change. `lines` are display lines, not a
structured address: the package renders them, it does not interpret them.

Three states fall out of the count:

| Points | Behaviour |
| --- | --- |
| none | No picker anywhere. Current behaviour. The order collects, full stop. |
| one | Auto-selected server-side the moment the collect option is stored. Rendered read-only. |
| several | Picker in the collect section and on the squeeze page. Required before pay (§D). |

### B. Cart-held state

Two keys in `cart.meta` (core's `AsArrayObject` bag, copied to `orders.meta` by
`FillOrderFromCart`):

- `fulfilment`: `'delivery' | 'collect'`. The customer's mode. Absent means delivery.
- `pickup_point`: the chosen point's `toArray()` snapshot, or absent.

Why the mode is stored separately from the option: the option cannot be stored until a shipping
address row exists (Problem 2), and the mode must survive a reload before that (Problem 1). Why
the cart and not `checkout_sessions`: both keys qualify the cart's shipping option, which is
already cart state (`cart_addresses.shipping_option`); the cart page needs to write them before
a session exists (§G); and core already carries `cart.meta` onto the order for free.

The snapshot, not just the handle, is stored so that the order and the panel stay agnostic: a
point renamed or removed later does not change what a placed order says.

A new action, `Actions\SetFulfilment`, is the only writer of these keys. It takes a `Cart`, not a
session, so the host's cart page can call it too:

```php
public function execute(Cart $cart, string $mode, ?string $pickupPoint = null): Cart
```

Behaviour:

- `mode = 'collect'`: write `meta.fulfilment`. If a shipping address row exists, resolve the
  cart's collect option through `ShippingManifest::getOptions($cart)->firstWhere('collect', true)`
  and store it via `$cart->setShippingOption()` (a collect-capable cart always has exactly one;
  a cart with none keeps its current option and the projection says collect is unavailable).
  Then apply the point: the given handle if valid, else the single point if the provider offers
  exactly one, else leave it unset.
- `mode = 'delivery'`: write `meta.fulfilment`, forget `meta.pickup_point`. If the stored
  option collects, replace it with the first non-collect option in manifest order (the same
  fallback `useCheckout.js` picks client-side today, moved server-side so the stored selection
  always matches the mode). If no non-collect option exists, null the stored option
  (`cart_addresses.shipping_option`) rather than leave a collect option under a delivery mode;
  the order cannot be created until the customer chooses again, which is the truthful state.
- A handle the provider does not offer for this cart throws `ValidationException` on
  `pickup_point`, the same shape `setShippingOption` uses for an unavailable option.
- Mode and option never disagree. `setShippingOption` with a non-collect option while the mode
  is collect flips the mode to delivery and forgets the point; with the collect option while
  the mode is delivery it flips to collect and applies the single point if there is one.
- Writes merge into `cart.meta`, never replace it. Implementation must audit wholesale
  `meta` writers (`Cart::update(['meta' => …])`) in the storefront package and host before
  relying on the keys surviving a session.
- A stored point the provider no longer offers for this cart (basket changed, branch removed)
  is treated as unselected: the projection returns `pickupPointId: null`, the validator
  fails, and the next `SetFulfilment` call forgets it.
- Supersede ([[0010-cart-session-reconciliation]] §A): nothing to carry. A superseding session
  is always minted for the same cart (`CreateCheckoutSession` selects siblings by
  `cart_reference`), and both keys live on that cart, so they survive by construction. A
  sign-in that swaps `CartSession::current()` to a different cart is the same accepted gap
  0011 §E documents for `element_data`: no cross-cart carry-over exists, and none is added here.

`LunarCheckoutDriver` gains `setFulfilment(CheckoutSession, string $mode): CartSnapshot` and
`setPickupPoint(CheckoutSession, string $handle): CartSnapshot`, both thin wrappers over
the action followed by `resync()`, dispatching `FulfilmentSet` and `PickupPointSet`
(`CheckoutSessionEvent` subclasses carrying the mode / handle). `storeShippingAddress` gains
one line: after the address is written, if `meta.fulfilment === 'collect'` and the stored
option does not collect, re-run the action for `'collect'` so the option lands as soon as the
row it needs exists.

### C. Getting the point onto the order (a shipping modifier, no driver change)

Core resolves shipping options through a pipeline of `ShippingModifier`s
(`ShippingManifest::getOptions()`), and every option is a mutable `ShippingOption` with a public
`meta` array. The package registers `Shipping\PickupPointModifier` on `ShippingModifiers` in
`boot()`. It acts **after** `$next($cart)` returns, so it runs once every other modifier has
pushed its options regardless of provider boot order:

```php
public function handle(Cart $cart, Closure $next): mixed
{
    $result = $next($cart);

    $point = $cart->meta['pickup_point'] ?? null;

    if (is_array($point)) {
        foreach (app(ShippingManifest::class)->options as $option) {
            if ($option->collect) {
                $option->meta = array_merge($option->meta ?? [], ['pickup_point' => $point]);
            }
        }
    }

    return $result;
}
```

That is the whole write path. `ShippingManifest::getOption()` re-resolves the option from the
cart at order creation, `CreateShippingLine` stamps the meta onto the shipping line, and the
placed order carries `order_lines(shipping).meta = {collect: true, …, pickup_point}`. It
works for any collect-capable driver, including a static `ShippingManifest::addOption()`, so the
package's own tests can assert the line meta without table-rate-shipping. The checkout package
does not touch the order and `OrderPlacing` listeners are not involved.
`orders.meta.pickup_point` also arrives via `FillOrderFromCart`; the shipping line copy is
the normative one, because that is where `collect` lives and what the fulfilment method reads.

### D. Validation through Lunar's seam

`Validation\Cart\PickupPointRequired extends Lunar\Core\Validation\BaseValidator`,
appended by `CheckoutServiceProvider::register()` to `lunar.cart.validators.order_create`
after core's `ValidateCartForOrderCreation`:

- fail on `fulfilment` when `meta.fulfilment` and the stored option disagree (collect mode
  with a non-collect option, or the reverse), which is only reachable by a writer that bypassed
  `SetFulfilment`;
- pass when the cart's stored option does not collect;
- pass when no provider is bound or it offers no points for this cart;
- otherwise fail on `pickup_point` unless `meta.pickup_point.handle` is one of the
  offered handles.

That makes `Cart::canCreateOrder()` authoritative, which is what `complete()` and
`assertReadyForPayment()` already consult. For a specific message on screen, both of those
run the same check first and throw `PaymentConfirmationException('pickup_point_required')`
ahead of the generic `cart_not_orderable`; the client maps the reason to inline copy on the
collect section. Element `rules()` stay static ([[0001-core-element-model]] §B); no element
is involved.

`computeFingerprint()` gains `'pickup_point' => $cart->meta['pickup_point']['handle'] ?? null`
and `'fulfilment' => $cart->meta['fulfilment'] ?? 'delivery'`, so a change to either after the
pay-boundary pin is a `fingerprint_mismatch` like any other payable change.

### E. Transport

`projectCheckout()` adds:

```
fulfilment:        'delivery' | 'collect'        // cart.meta.fulfilment; when absent, derived
                                                 // from the stored option (carts predating this)
pickupPoints:  [{ id, name, lines }]         // provider output, [] when none
pickupPointId: string | null                 // cart.meta.pickup_point.handle
urls.fulfilment:   POST {path}/{session}/fulfilment        { fulfilment }
urls.pickupPoint: POST {path}/{session}/pickup-point { pickup_point }
```

Both routes are ownership-gated and `ensureOperable`, return `back()`, and sit beside
`shipping-option` in `routes/checkout.php`. `shippingMethods[]` is unchanged; option meta is
still not projected, the point travels on its own key.

The express start JSON response (spec 0012 §C) carries the same three keys, so the squeeze page
renders from one projection.

### F. UI

`useCheckout.js`: `state.fulfilment = data.fulfilment ?? 'delivery'`, `state.pickupPoints`,
`state.pickupPointId`. `setFulfilment(mode)` posts to `urls.fulfilment` with an optimistic
flip and rollback on error; it no longer posts a shipping option itself, the server does (§B).
New `selectPickupPoint(id)` mirrors `selectShipping()`. Derived: `collectAvailable`
(a collect option exists), `pickupPointRequired`
(`fulfilment === 'collect' && pickupPoints.length > 1 && !pickupPointId`), which
disables the pay button with the reason shown inline.

`LunarCheckout.vue` collect branch becomes `PickupSection.vue`:

1. The address block. The same `DeliverySection` component in a collect variant: heading
   "Your details", sub-copy "We'll use this as your billing address", same saved-address
   picker, same manual form, same write route. It stores the cart's shipping address, which is
   what Lunar needs before the collect option can be stored (§B) and what the payment step
   already defaults billing to. This is also legacy parity: the old checkout set delivery and
   billing from the same submitted data on collection orders.
2. The point block. Hidden when `pickupPoints` is empty (today's locked sentence stays,
   minus the deep link). A single point renders as a locked row with its name and lines.
   Several render as radio rows in the `ShippingMethods` style, name in bold, lines beneath,
   selection posting through `selectPickupPoint`. Unavailable collect keeps today's copy.

`ExpressConfirm.vue` "Collect from" row shows the chosen point's name and lines when there is
one, else the method name as now. With several points the row is editable inline like the
shipping-method row (radio list, saves through the same route); with one or none the Change
link is removed rather than deep-linking to nothing.

Known limitation, by design: the collect option is zone-resolved like any other, so a shipping
address outside the zone (a customer abroad wanting to collect in London) sees collect as
unavailable. Legacy forced the country to United Kingdom; this spec does not. A host that needs
that can add a modifier.

`FulfilmentToggle.vue` is unchanged. Spec 0012 §G stands: a wallet sheet started in collect
mode requests no shipping; the wallet's billing address is written as billing and, when the
cart has no shipping address row, also as the shipping address so the collect option can be
stored (the address is the customer's own either way).

### G. The host's cart page

A host that offers a delivery / collect switch before checkout (a basket page) calls
`Actions\SetFulfilment` against the cart directly from its own endpoint. No session is needed
and `start()` is unchanged: the checkout projects the cart's mode and point on first render.
The host owns that endpoint's route and auth; the action is the reuse boundary.

### H. Panel (sibling change: `panel`)

`OrderShowController::shippingOption()` reads the shipping line in both branches and adds
`collect: bool` and `pickup_point: ?{handle, name, lines}` from `$line->meta`. The order
screen's Shipping section swaps the truck icon for the store icon when `collect` is true and
renders "Collect from {name}" with the lines beneath the method name. Nothing else changes:
the fulfilment card already badges the method and drives its states; `location` stays the stock
location, which is a different concept from the pickup point and deliberately not conflated.

The order's shipping address on a collect order is the customer's own address (§F.1). Anything
that renders it as "Delivery address", in this panel or in a host, must check `collect` first
and say so. The panel's Shipping address section is relabelled "Customer address" when the
shipping line collects.

## Testing

Package (feature, alongside the existing checkout suite):

- fulfilment mode: set collect on a fresh guest cart without an address → stored, no option
  yet, projection says collect; save an address → collect option stored; set delivery → option
  falls back to cheapest courier and the point is forgotten.
- provider states: unbound → no picker keys and no validation; one point → auto-selected on
  storing collect; several → none selected until posted; unknown handle → 422.
- fingerprint changes when the point changes; pay after a point change with the old
  fingerprint → `fingerprint_mismatch`.
- pay with several points and none chosen → `pickup_point_required`; with one chosen →
  order placed and `order_lines(shipping).meta.pickup_point` equals the snapshot,
  `orders.meta.pickup_point` too.
- the modifier stamps `pickup_point` onto every collect option and never onto a delivery
  option; a cart without a point leaves option meta untouched.
- projection hydrates `fulfilment` from the cart on reload.
- express start response carries the three keys; squeeze page confirm in collect mode with a
  point places the order with the point on the line.

`panel`: `OrderShowController` exposes `collect` and `pickup_point` for a collect order and
`collect: false` for a delivery order.

## Out of scope

- Offline / pay-later for collect orders. `PaymentMethods\Offline` already exists as the
  mechanism; when it is wanted, the host registers it with an `appliesTo()` that reads the
  stored option's `collect` flag, exactly as its docblock illustrates. The decision for the
  first storefront is that offline payment belongs to on-account orders, which are a separate
  piece of work.
- Per-point availability (stock at a branch, opening hours, cut-offs). The provider gets the
  cart, so a host can filter; nothing here schedules.
- Making the pickup point a first-class core concept (a `ShippingOption` field, a
  `lunar_locations` link). `meta` is the seam core already provides for exactly this kind of
  option qualifier, and the fulfilment location remains the stock location.
- Cart merge on sign-in. `meta.fulfilment` and `meta.pickup_point` follow the same fate
  as `cart_addresses.shipping_option` across a merge, whatever core does with it; no special
  carry-over is added.
