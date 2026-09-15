# 0083 — Cart-line fulfilment method: choosing how a line is fulfilled at cart time

- Status: draft
- Author: Glenn Jacobs (with Claude)
- Created: 2026-09-10
- TODO item: Cart-line fulfilment method — cart default + per-line override, keyed on registered fulfilment methods

## Problem

[[0031-fulfilment-methods]] made fulfilment *flow* pluggable — `shipping`, `collection`,
`digital`, or anything a consumer registers against `FulfilmentMethodManifest`. But the
decision of **which** method a line lands in is made after the order is placed, by
`EnsureInitialFulfilment` walking the registered methods in priority order and letting each
`claim()` the lines it recognises. The only inputs to that decision are:

- two capability flags snapshotted onto the order line from the purchasable —
  `requires_shipping` (`isShippable()`) and `requires_fulfilment` (`requiresFulfilment()`).
  They say what a product *can* be, not how the customer wants it;
- one cart-wide signal — the chosen shipping option's `collect` flag, copied into the
  shipping order line's `meta` by `CreateShippingLine` and read back out by
  `Drivers\FulfilmentMethods\Collection::claim()`.

Nothing on the cart or the cart line says how a line is to be fulfilled. Consequences:

- **No per-line choice.** A cart cannot express "deliver these, collect that one". The
  `collect` flag is all-or-nothing, so a trade customer who wants the bulky item delivered
  and the small one held at the counter has to place two orders.
- **Intent is smuggled through shipping.** Fulfilment intent only exists as a side effect
  of picking a shipping *rate*. A digital-only cart has no shipping option and therefore no
  way to express intent at all; a consumer-registered method (a prescription flow, a 3PL
  hand-off) cannot be *chosen* by the customer — it can only infer from the order after the
  fact.
- **Nothing to read.** A storefront cannot tell the customer, before the order exists, how
  a line will be fulfilled. `$cartLine` has no accessor for it; the answer is implicit in
  `isShippable()` plus whichever rate is selected.
- **The common case is implicit.** "Fulfil the whole cart one way" — what most storefronts
  want — already works, but only by picking a shipping option. There is no verb that says
  it directly.

Two existing mechanics constrain the fix. Cart-line identity is *purchasable plus a `meta`
diff* (`GetExistingCartLine`, `CreateOrderLines`, `MergeCart`), so a per-line preference
must join that identity or lines with different preferences merge. And order lines are
truthful snapshots by design ([[0030-fulfillable-order-lines]]), so the resolved preference
must be stamped at order creation, not re-derived from a mutable cart.

Pre-release this is three nullable baseline columns and two additive contract methods.
Post-release it is a schema migration, a contract break on `FulfilmentMethod` and on the
add-to-cart action seam, and a Rector cycle. Decide now.

## Proposal

Add a first-class **requested fulfilment method** at two levels — a cart-wide default and
a per-line override — keyed on the registered `FulfilmentMethod` keys, resolved through
one precedence chain, and snapshotted onto the order line so the initial-fulfilment pass
routes explicitly-keyed lines straight to their method. Lines with no explicit method keep
today's claim-based inference, so existing storefronts behave exactly as they do now.

### A. Schema

Three nullable, indexed string columns holding a `FulfilmentMethod` key (the same shape as
`fulfilments.method`):

| Table | Column | Means |
|---|---|---|
| `carts` | `fulfilment_method` (after `coupon_code`) | the cart-wide default, set explicitly by the storefront |
| `cart_lines` | `fulfilment_method` (after `quantity`) | the per-line override; null inherits the cart default |
| `order_lines` | `fulfilment_method` (after `requires_fulfilment`) | the *resolved* method at order creation; null means "inferred at fulfilment time" |

All three are nullable with no default. Null is meaningful: "nothing requested". Models gain
the matching `@property ?string $fulfilment_method`; `CartLineFactory` and
`OrderLineFactory` default it to null so existing tests are untouched.

### B. `FulfilmentMethod` contract additions

Two methods, both additive with obvious core implementations:

```php
interface FulfilmentMethod
{
    // ...existing surface unchanged...

    /**
     * Whether this method can fulfil the given purchasable. Consulted when a
     * method is requested for a cart line (validation) and when the cart-wide
     * default is applied to a line (a default that cannot fulfil the line is
     * skipped, not forced).
     */
    public function supports(Purchasable $purchasable): bool;

    /**
     * Whether a fulfilment of this method is delivered to the customer — it
     * needs a shipping option and a shipping address at checkout, and its lines
     * count towards shipping-rate calculation.
     */
    public function requiresDelivery(): bool;
}
```

Core mapping:

| Method | `supports()` | `requiresDelivery()` |
|---|---|---|
| `shipping` | `$purchasable->isShippable()` | `true` |
| `collection` | `$purchasable->isShippable()` | `false` |
| `digital` | `$purchasable->requiresFulfilment() && ! $purchasable->isShippable()` | `false` |

`supports()` mirrors each method's `claim()` predicate over a single purchasable rather
than an order line — the same rule, asked earlier. The existing `claim()` stays as the
inference fallback (section E).

`FulfilmentMethodManifest` gains one query helper, for storefront pickers and the validator:

```php
/** @return Collection<string, FulfilmentMethod>  keyed by method key, in priority order */
public function supporting(Purchasable $purchasable): Collection;
```

### C. Resolution — one precedence chain

A single internal action, `Actions\Fulfilment\ResolveCartLineFulfilmentMethod`
(contract `ResolvesCartLineFulfilmentMethod`, registered in `ActionServiceProvider`),
answers "which method has this line asked for". It is orchestration-only — no model verb —
and the model accessors delegate to it:

```php
Cart::fulfilmentMethod(): ?FulfilmentMethod
    // 1. the explicit carts.fulfilment_method, resolved via the manifest
    // 2. else derived from the chosen shipping option: collect ? 'collection' : 'shipping'
    // 3. else null

CartLine::fulfilmentMethod(): ?FulfilmentMethod
    // 1. the explicit cart_lines.fulfilment_method
    // 2. else the cart default (above), but only if it supports($line->purchasable)
    // 3. else null   — "inferred at fulfilment time"
```

Step 2 of the line chain is what keeps digital goods correct with no storefront change: a
cart whose default is `shipping` (explicit or derived) has a licence key in it; `shipping`
does not support it, so the line resolves to null and `Digital::claim()` picks it up at
order time exactly as today. A default is a preference, never a coercion.

An explicit key that no longer resolves (the method was unregistered after the cart was
built) resolves to null at that step and falls through. It is not an error: the cart is
still orderable, and the claim pass handles the line.

### D. Cart surface

**Adding a line.** `Cart::add()` and `addLines()` gain an optional named argument, appended
so positional callers are unaffected; the action contract gains it too:

```php
$cart->add($variant, quantity: 2, fulfilmentMethod: 'collection');
$cart->addLines([['purchasable' => $variant, 'quantity' => 2, 'fulfilment_method' => 'collection']]);

interface AddsOrUpdatesPurchasable
{
    public function execute(Cart $cart, Purchasable $purchasable, int $quantity = 1, array $meta = [], ?string $fulfilmentMethod = null): void;
}
```

**Changing it.** Two new model verbs, each one-line-delegating to an action contract
([[0029-entry-point-conventions]]); `updateLine()` / `UpdatesCartLine` are untouched:

```php
$cart->setFulfilmentMethod(?string $key, bool $refresh = true): Cart;        // SetsCartFulfilmentMethod
$cartLine->setFulfilmentMethod(?string $key): CartLine;                      // SetsCartLineFulfilmentMethod
```

Passing null clears the value (back to "inherit" / "nothing requested"). Both run a
validator chain first, mirroring `setShippingOption()`:

- `config('lunar.cart.validators.set_fulfilment_method')` — default
  `Validation\Cart\CartFulfilmentMethod`: the key is null or registered.
- `config('lunar.cart.validators.set_line_fulfilment_method')` — default
  `Validation\CartLine\CartLineFulfilmentMethod`: the key is null or registered, **and**
  `supports($purchasable)`.
- `add_to_cart` gains `CartLineFulfilmentMethod` after `CartLineAvailability`, receiving the
  `fulfilmentMethod` parameter alongside `purchasable`.

Failures throw a `CartException` with two new translated messages:
`carts.fulfilment_method_unknown` and `carts.fulfilment_method_unsupported`.

**Identity and fingerprint.** The method joins the line identity everywhere the `meta` diff
is used today: `GetExistingCartLine`, `CreateOrderLines`' existing-line match, and
`MergeCart`. The same variant requested for delivery and for collection is two lines.
`GenerateFingerprint` includes each line's `fulfilment_method` and the cart's, so a changed
preference invalidates a checkout fingerprint like a changed quantity does.

**Delivery lines.** The lines that actually need delivering:

```php
Cart::deliveryLines(): Collection<int, CartLine>
```

A line is a delivery line when its *explicit* method (line override, else the explicit cart
default where supported) `requiresDelivery()`, or when it has no explicit method and
`isShippable()`. The shipping-option-derived step of the chain is deliberately **not**
consulted here — shipping-rate calculation reads `deliveryLines()`, and the rate being
calculated cannot be an input to which lines it covers. `Cart::isShippable()` keeps its
name and re-keys to `deliveryLines()->isNotEmpty()`; behaviour only changes when an
override is present.

### E. Order side

**Stamp.** `CreateOrderLines` sets `'fulfilment_method' => $cartLine->fulfilmentMethod()?->getKey()`.
The full chain applies here (the shipping option is fixed by now), so a collect-option cart
stamps `collection` on its physical lines and the order line is self-describing — the
merchant, the panel and any consumer reading the line see the requested method before a
fulfilment exists.

**Assignment.** `EnsureInitialFulfilment` gains a first pass ahead of the claim loop:

1. **Explicit pass.** Group the fulfillable lines carrying a `fulfilment_method` by key. For
   each key that resolves to a registered method, create one fulfilment in that method's
   `defaultState()` covering those lines at full quantity, and remove them from the pool. A
   key that no longer resolves leaves its lines in the pool.
2. **Claim pass.** Today's priority-ordered `claim()` loop over whatever remains.

`Collection::claim()` keeps reading the shipping line's `collect` flag as the fallback for
lines with no stamp (orders created by paths other than the cart pipeline, v1-upgraded
orders). Nothing about `claim()` implementations changes; a consumer method becomes
selectable at cart time with no code beyond `supports()` / `requiresDelivery()`.

The existing `FulfilmentQuantity` guard (`requires_fulfilment` must be true) still applies —
the explicit pass draws from `fulfillableLines()`, so a consumer method that `supports()` a
non-fulfillable service line never gets a fulfilment for it.

### F. Checkout validation

`ValidateCartForOrderCreation` re-keys its shipping rules:

- a shipping option is required when `$cart->isShippable()` (unchanged in name, now
  delivery-line based);
- a shipping address is required when there are delivery lines **and** the chosen option is
  not a collection — today's rule, expressed over `deliveryLines()`.

A cart whose every physical line is overridden to `collection` therefore needs neither a
shipping option nor a shipping address, which is the case the current model cannot express.

### G. Shipping-rate calculation over delivery lines

The table-rate drivers and resolver sum weight, subtotal, stock and exclusions over
`$cart->lines`. They switch to `$cart->deliveryLines()`:

- `Drivers\ShippingMethods\{FlatRate,FreeShipping,ShipBy}` — subtotal thresholds and
  product exclusions;
- `Resolvers\ShippingRateResolver` — `cartWeightKg` and `allCartItemsAreInStock`;
- `Models\ShippingRate` — the per-line loop.

`Drivers\ShippingMethods\Collection` keeps `$cart->lines`: its exclusion check asks whether
anything in the cart cannot be collected, which is a whole-cart question. A line collected
from the counter no longer adds weight to the parcel rate, and a free-shipping threshold is
met by what is actually shipped.

### H. Admin

None required in core. The panel order view is fulfilment-centric (spec 0067) and every
fulfilment card already badges its method; the requested method on the order line only
differs from the fulfilment's method after a merchant moves lines by hand. The Filament
bridge and the panel may surface `order_lines.fulfilment_method` as a line subline later —
it is a read of an existing column, not part of this spec.

## Alternatives considered

- **A `meta['fulfilment_method']` convention, no schema.** Costs nothing up front but is
  untyped, unvalidated, invisible to `EnsureInitialFulfilment`, and `meta` is the consumer's
  bag — core would be squatting a key in it. Rejected; it is what a consumer can already do
  today with a custom `claim()`, and that it is possible is exactly why it is undiscoverable.
- **Per-line shipping options (commercetools "multiple" shipping mode).** Each line targets
  an address and a rate; carts become sets of shipments. Far larger, and the wrong
  granularity: the customer-facing choice is *how* a line is fulfilled, and the rate remains
  a cart-wide price. Shopify's delivery groups reach the same conclusion. Rejected; a later
  multi-address spec can build on the per-line key this one introduces.
- **Cart default only, no line override.** Covers the common case but is trivially
  derivable from the shipping option today; the whole value is in the line override.
  Rejected.
- **Make the shipping option own the intent** (`ShippingOption::fulfilmentMethod`, stamped
  onto the cart by `SetShippingOption`). Two writers for one value — the option and the
  explicit setter — drifting apart, the pattern [[0082-selling-policy-rework]] removes
  elsewhere. Rejected in favour of a derived read (section C, step 2).
- **Make the cart default coerce every line.** Simpler chain, but a `shipping` default would
  stamp `shipping` on a licence key and the order would need a parcel for it. Rejected: a
  default is a preference applied where it can be, never a coercion (section C).
- **Do nothing.** Ships v2.0 with fulfilment intent expressible only through a shipping
  rate, no per-line choice, and turns three nullable baseline columns into a post-release
  migration plus two contract breaks. Rejected.

## Migration impact

- **Database** (baseline edits, v2 pre-release): `carts.fulfilment_method`,
  `cart_lines.fulfilment_method`, `order_lines.fulfilment_method` — nullable string,
  indexed. No new tables, no backfill.
- **Breaking changes to the public contract surface:**
  - `Contracts\FulfilmentMethod` gains `supports()` and `requiresDelivery()`. Consumers
    implementing the contract must add them; Rector note in `LunarSetList`.
  - `Contracts\Actions\Carts\AddsOrUpdatesPurchasable::execute()` gains a trailing
    `?string $fulfilmentMethod = null`. Consumers who bound their own implementation must
    add the parameter; Rector note.
  - `Cart::isShippable()` re-keys to delivery lines — behavioural only when a line override
    is present.
  - Cart-line identity now includes `fulfilment_method` — a consumer relying on two lines
    with the same purchasable and `meta` always merging sees them stay separate only when
    they carry different methods.
  - New surface (additive): `Cart::fulfilmentMethod()`, `Cart::setFulfilmentMethod()`,
    `Cart::deliveryLines()`, `CartLine::fulfilmentMethod()`,
    `CartLine::setFulfilmentMethod()`, `FulfilmentMethodManifest::supporting()`,
    `Actions\Carts\{SetCartFulfilmentMethod,SetCartLineFulfilmentMethod}`,
    `Actions\Fulfilment\ResolveCartLineFulfilmentMethod` and their contracts,
    `Validation\Cart\CartFulfilmentMethod`, `Validation\CartLine\CartLineFulfilmentMethod`,
    the two validator config keys, and the `fulfilmentMethod` argument on `add()` /
    `addLines()`.
- **Upgrade path (`packages/upgrade`)**: no data step — the columns are nullable and v1
  carries no equivalent; v1 orders keep inferring via `claim()`. Rector rules for the two
  contract changes above.
- **Translations (16 locales)**: `exceptions.carts.fulfilment_method_unknown` and
  `exceptions.carts.fulfilment_method_unsupported` in `core` — English first, translated
  into the other 15.
- **Filament / admin impact**: none (section H).

## Open questions

1. **Collection location per line.** A line overridden to `collection` collects from the
   order's resolved location, as 0031 left it. Choosing *which* counter per line is deferred
   to the location-scoped availability and stock routing item (TODO Ideas), which owns
   per-location selection at sell time. Confirm at review that this spec should not reserve
   a column for it.
2. **A collect rate with a line overridden to `shipping`.** The override wins for that line
   (it becomes a delivery line, an address is required, the collect rate still prices the
   cart). Consistent but odd; the storefront is expected to filter rates against
   `deliveryLines()`. Decide whether the order-creation validator should reject the
   combination instead. Owner: slice 4.
3. **Exposing the likely inferred method.** When a line resolves to null, a storefront may
   want to show what `claim()` will probably do. `FulfilmentMethodManifest::supporting()`
   gives the candidates in priority order; whether the first candidate should be surfaced
   as a "probable method" accessor is left to the storefront API spec.

## References

- [[0031-fulfilment-methods]] — the method seam, `claim()`, the `collect` flag on the
  shipping line, and the open question on collection location this spec touches.
- [[0030-fulfillable-order-lines]] — the snapshot reasoning the `order_lines.fulfilment_method`
  stamp follows.
- [[0045-optional-purchasables-and-shipping-de-morph]] — the self-describing shipping line
  and the `collect` meta the fallback claim reads.
- [[0029-entry-point-conventions]] — model verbs delegating to action contracts.
- [[0016-service-layer-di]] — actions bound in `ActionServiceProvider`.
- [[0082-selling-policy-rework]] — the single-writer stance the derived cart default
  follows.
- `Actions/Carts/GetExistingCartLine.php`, `Actions/Carts/MergeCart.php`,
  `Pipelines/Order/Creation/CreateOrderLines.php` — the three sites where line identity is
  a purchasable plus a `meta` diff.
- Prior art: Shopify cart delivery groups and `FulfillmentOrder.deliveryMethod`;
  commercetools `Cart.shippingMode = Multiple` with per-line `ItemShippingDetails`;
  Saleor `Checkout.deliveryMethod` (cart-wide only).

## Implementation plan

- [ ] Slice 1 — `FulfilmentMethod::supports()` / `requiresDelivery()` on the contract and
  the three core drivers, `FulfilmentMethodManifest::supporting()`, tests.
- [ ] Slice 2 — cart side: the three baseline columns, model properties and factories, the
  resolution action + accessors, `add()` / `addLines()` argument, the two setter verbs and
  actions, validators and config keys, identity in `GetExistingCartLine` / `MergeCart`,
  fingerprint, tests.
- [ ] Slice 3 — order side: `CreateOrderLines` stamp and identity match, the explicit pass
  in `EnsureInitialFulfilment`, tests.
- [ ] Slice 4 — delivery lines: `Cart::deliveryLines()`, `isShippable()` re-key,
  `ValidateCartForOrderCreation`, table-rate drivers and resolver over delivery lines,
  tests in the `core` and `shipping` suites (resolves open question 2).
- [ ] Slice 5 — the two exception keys across 16 locales, Rector rules in the upgrade
  package, docs.
