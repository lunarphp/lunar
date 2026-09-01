# 0011 — Address lookup drivers & making the element bag real

- Status: shipped (2026-09-01)
- Author: Alec Ritson
- Created: 2026-08-19
- TODO item: "Checkout address lookup — a driver seam for postcode → address, shipped with a first concrete driver; make the [[0010-cart-session-reconciliation]] §C element bag the actual persistence for price-neutral elements"

## Problem

Two gaps surfaced while porting a B2B storefront's checkout (PO reference and
delivery notes captured at checkout, UK postcode lookup on the address step).
Neither is client-specific; both are holes in this package.

**1. There is no address lookup seam at all.** `DeliverySection.vue` renders an
input labelled "Start typing a postcode or street…" above a comment admitting it
is presentational. Every UK storefront wires a lookup vendor; the package
currently forces each one to fork the delivery section to do it.

**2. The element bag specified in [[0010-cart-session-reconciliation]] §C does
not exist.** `Models\CheckoutSession` has the `element_data` column and
`putElementData()` / `getElementData()`, and **nothing calls them**. What
elements actually write to is `Session\CheckoutSession`, the Laravel-session
prototype whose own docblock says it is "Replaced by the spec 0004
`CheckoutSession` model … when it lands". The model landed. Consequences:

- Captured element data lives in the visitor's PHP session, not on the session
  row — so it is invisible to anything that reads the session, and there is no
  row to serialise writes on (0010 §C requires exactly that).
- `POST /checkout/elements/{handle}` is the **only** write route in the package
  with no `{session}` segment and no ownership gate. Every neighbour route
  resolves the session and calls `ensureOwnership()`; this one takes a handle and
  trusts the caller.
- `CreateCheckoutSession` copies `metadata` onto a superseding session but not
  `element_data`. Superseding is the *normal* path when a guest signs in and the
  cart merges ([[0010-cart-session-reconciliation]] §A), so anything an element
  captured before sign-in is silently discarded.
- Nothing projects captured data onto the order. [[0001-core-element-model]] §I
  specifies an `OrderPlacing` event for this; it is not implemented.

## Proposal

Two independent additions, sharing one release because the second is what makes
custom elements usable and the first is the element most consumers want next.

**Address lookup is a named driver**, resolved through a manager from config,
exactly like the checkout driver of [[0004-checkout-session]] — a value in
config, never a class swap. The package ships a null driver (the default) and one
real driver, so the seam ships proven rather than hypothetical.

**The element bag becomes the persistence for price-neutral elements**, backed by
the session row under a lock, reached through a store abstraction whose name says
what it is.

### A. The lookup contract

`Contracts\AddressLookup`, one method:

```php
public function lookup(string $postcode): array; // list<CheckoutAddress>
```

Returns existing `DataObjects\CheckoutAddress` instances — the package's address
shape already, so no new DTO and no mapping at the UI boundary. An empty array
means "no addresses at that postcode", which is a legitimate answer, not an
error. Vendor failures throw `Exceptions\AddressLookupException`; the transport
layer (§C) turns that into a generic message.

**Postcode lookup only.** Type-ahead (`suggest()` / `resolve()`) is deliberately
excluded: it doubles the contract before a second driver exists to validate the
shape, and it bills per keystroke. When it arrives it arrives as an additive
capability interface — the same pattern payment methods use
([[0002-payment-methods-and-driver]]), never as extra methods on this contract.

### B. Manager, drivers & config

`AddressLookupManager` mirrors `CheckoutSessionManager`: resolves the active
driver by name, the contract binds to `->driver()`, hosts add their own with
`extend()`.

```php
'address_lookup' => [
    'driver' => env('CHECKOUT_ADDRESS_LOOKUP_DRIVER', 'null'),

    'ideal_postcodes' => [
        'key' => env('IDEAL_POSTCODES_KEY'),
        'cache_ttl' => 60 * 60 * 24 * 30,
    ],
],
```

- `AddressLookup\NullLookup` — the default. Returns an empty list and reports
  itself unavailable.
- `AddressLookup\IdealPostcodes` — `Http` client against
  `api.ideal-postcodes.co.uk/v1/postcodes/{postcode}`, mapping the vendor's
  `line_1/line_2/post_town/county/postcode` onto `CheckoutAddress` with
  `country_code: GB`.

Results are cached (`Cache::remember`, key = normalised postcode, TTL from
config). Postcodes are static data and the vendor bills per lookup, so repeat
lookups of the same postcode must not repeat the charge.

**Availability is projected, not guessed.** The session render exposes whether a
usable driver is configured; `DeliverySection.vue` renders the postcode search
**only** when it is, and the presentational placeholder input is deleted. A store
with no lookup driver gets honest manual-entry fields instead of a decoy.

### C. Transport

`POST /checkout/{session}/address-lookup` — session-scoped, `ensureOwnership()`,
throttle bucket `checkout-address-lookup` (10/min/IP, mirroring
`checkout-contact-lookup`). Validates UK postcode format **before** any vendor
call. Returns `{ addresses: [...] }` as JSON.

The gate and the throttle are not ceremony: an ungated lookup endpoint on a
per-lookup-billed vendor is a way for a stranger to spend the merchant's money.
`AddressLookupException` and vendor 5xx both return a generic "couldn't search"
payload — the vendor's response body never reaches the browser.

Selecting a returned address fills the delivery form client-side; it does not
write the cart. Persistence stays the existing `shipping-address` route, so
address writes keep one path.

### D. The element data store

The contract elements use is a key/value store for captured data. It is **not**
the checkout session, and calling it `CheckoutSession` — while
`Models\CheckoutSession` and `Session\CheckoutSession` also exist — makes
"is `$session` the row or the store?" a coin flip at every call site. Renamed:

| Was | Becomes |
| --- | --- |
| `Contracts\CheckoutSession` | `Contracts\ElementDataStore` |
| `Session\CheckoutSession` | `Session\SessionElementStore` |
| — | `Session\ModelElementStore` |
| `CheckoutElement::setSession()` | `CheckoutElement::setDataStore()` |

`CheckoutSession` then means the row, everywhere.

`ModelElementStore` wraps a `Models\CheckoutSession` and is the binding for the
session flow. `putElementData()` as written is a read-modify-`save()` of the
whole JSON blob with no lock — 0010 §C requires bag writes serialise on the
session row, so it moves inside a transaction with `lockForUpdate()` on
`checkout_sessions`. `SessionElementStore` survives for the session-less embedded
flow, which per 0010 §C has no bag.

**Deviation recorded:** 0010 §C says elements reach the bag "through the context
— `$context->putElementData($handle, $data)` — never the session model directly".
The [[0001-core-element-model]] context verb table is not implemented; elements
receive a store via `setDataStore()`. This spec keeps that shape and treats
context mediation as outstanding 0001 work, so the bag can become real without a
context refactor riding along.

### E. Bag carry-over on supersede

`CreateCheckoutSession` copies the voided sibling's `element_data` onto the new
session alongside `metadata`. A guest who typed a PO number, then signed in and
had their cart merged, keeps it.

### F. `OrderPlacing`

[[0001-core-element-model]] §I specifies `OrderPlacing` firing inside
`complete()` "immediately before order creation, on both the sync and async
paths". **Amended here:** it fires immediately before order **placement**.

Since 0001 was written, `complete()` adopts an order the gateway already created
during `authorize()` (the webhook-first race) rather than minting a second one, so
on the async path there is no creation left to precede. The one point both paths
converge is after the order resolves and before `placed_at` is stamped. Firing
there is inside the existing transaction and cart-row lock, so a listener's
writes are atomic with placement and happen exactly once per order.

`CheckoutElementStored` (also 0001 §I) fires after any successful element store,
alongside the granular 0010 §G event.

### G. `OrderDetails` — the first bag-backed element

`Elements\OrderDetails` (handle `order-details`, region `main`, title "Order
details") captures a **customer PO reference** and **delivery notes** — the two
fields every B2B checkout asks for, and the first thing in this package that
persists through the bag rather than the cart. Component `OrderDetails.vue`,
registered in the package's `app.js` beside `contact-information` and
`offline-notice`.

Static `rules()` ([[0001-core-element-model]] §B — no branching on persisted
state): `reference` → `nullable|string|max:255`, `notes` →
`nullable|string|max:2000`. **Both optional.** Requiring a PO reference for
on-account payment is a plausible merchant rule and is deliberately not
supported here: it would branch on the selected payment method, which static
rules forbid, so it belongs at the pay boundary as its own piece of work.

Like every element, it is opt-in — a host registers it, and registering nothing
is how a host declines it. The package does **not** project the captured values
onto the order: what `customer_reference` and `notes` mean to a merchant's
downstream systems is theirs to decide, so they listen to `OrderPlacing` (§F).

## Testing

- **Manager/config:** driver resolution by name, `null` default, `extend()`
  registration, unknown driver throws.
- **`IdealPostcodes`:** faked `Http` — hit maps every field, empty result is an
  empty list not an error, vendor 4xx/5xx raises `AddressLookupException`, second
  identical lookup is served from cache with no second request.
- **Transport:** cross-customer session → 403, unowned → 403, malformed postcode
  → 422 with no vendor call, throttle bucket enforced, vendor failure returns the
  generic payload and leaks no vendor body.
- **Availability projection:** null driver → no search UI in the rendered props.
- **`ModelElementStore`:** round-trip through the row, concurrent writes to two
  handles both survive (no lost update), embedded flow still works on
  `SessionElementStore`.
- **Supersede:** bag carried onto the new session.
- **`OrderPlacing`:** fires once on the sync path; fires once on the
  webhook-first path where the order pre-exists; carries the order and the
  session.
- **`OrderDetails`:** both fields optional (empty store is valid), over-length
  input rejected, captured values survive a re-render as the form seed.

## Out of scope

- Type-ahead / autocomplete lookup (§A).
- Google Places as a driver — a consuming storefront's legacy checkout used it
  alongside Ideal Postcodes; postcode lookup covers the same job for UK trade
  addresses and one real driver is enough to prove the seam.
- The 0001 context verb table (§D deviation).
- Contributed frontend chunks. [[0009-frontend-element-extension]] assumes a host
  can ship its own element component, but the checkout's npm package is
  `@lunarphp/checkout-app`, `"private": true`, and exports nothing — there is no
  consumable entry point or vite plugin equivalent to `@lunarphp/panel` /
  `@lunarphp/panel-vite-plugin`. Until that exists, host-facing elements ship
  in-package and are opted into by registration.
