# 0012: Express payments & the confirm (squeeze) page

- Status: accepted (2026-09-03)
- Author: Alec Ritson
- Created: 2026-09-03
- Design reference: `checkout_designs/checkout_express_confirm.html` (squeeze page),
  `checkout_designs/screenshots/01-xp.png` (express region), spec [[0002-payment-methods-and-driver]]
  §C (the reserved express seam: `supportsExpress()` / `expressComponent()`).

## Problem

The checkout renders a presentational express-wallet stub (`ExpressWallets.vue`: hardcoded
Apple Pay / Google Pay / PayPal buttons that do nothing). The reference design commits to more
than buttons: a wallet sheet that collects address and shipping choice, followed by a **confirm
page** ("squeeze page") where the customer reviews everything, fills in what the wallet did not
share (name, PO reference, delivery instructions), and only then commits: *"Your card's
authorised with Apple Pay, a hold for £272.00, charged only when you tap Confirm & pay."*

Constraints the design imposes:

1. **The hold copy must be honest.** A review page after a wallet sheet only works when the
   sheet authorises without capturing. Gateways confirm at sheet close; the money movement must
   therefore be an authorise-only hold, captured at Confirm & pay.
2. **Edits after the hold can change the total.** The squeeze page allows address, shipping and
   fulfilment changes. Capture can take less than the authorised amount, never more, so a risen
   total needs either an incremental authorization (card rails, eligibility-gated) or a wallet
   re-open (the design's existing "Re-open wallet" affordance).
3. **Driver-based, not Stripe-based.** A merchant may run express through a standalone PayPal
   package (not PayPal-on-Stripe). Which wallets appear inside Stripe's element is Stripe
   dashboard config; *which gateways contribute to the express region* is a package seam.
4. **Two entry points**: the checkout page (above Contact, per design) and the cart page. The
   product page is out of scope for v1.

## Proposal

Express is a thin layer over the machinery specs 0002/0004/0010 already built. The squeeze page
is the **same checkout session in a different presentation**: the session stays `Open` through
the wallet sheet and every squeeze-page edit, the hold is an advisory intent exactly like the
standard flow's (0010 §E/§F), and Confirm & pay is the existing pay boundary (pin, then
`PaymentProcessing`, then `Completed` through the existing processing/reconcile path). No new
session state. The state machine tracks money-safety states, not UI location.

### A. `SupportsPaymentHolds` (core capability)

New opt-in contract in `lunar/core` beside `CreatesPaymentIntents` / `SupportsPaymentIntents` /
`SyncsPaymentIntents`, checked via `instanceof`, never a required part of `PaymentType`:

```php
interface SupportsPaymentHolds
{
    /**
     * Create (or resume) the cart's authorise-only intent: capture deferred,
     * amount adjustment requested where the rail offers it. Idempotent per
     * cart, like CreatesPaymentIntents::createIntent().
     */
    public function createHold(Cart $cart): PaymentIntentDescriptor;

    /**
     * Bring an authorised hold to the cart's new payable total. Ok when the
     * hold now covers it; NeedsReauthorization when this hold cannot stretch
     * (the customer re-opens the wallet for a fresh authorisation).
     */
    public function adjustHold(string $reference, int $amountMinor): HoldAdjustment;

    /**
     * Capture an authorised hold for the final amount (<= authorised).
     * Idempotent per reference. MUST throw when the gateway cannot confirm
     * the capture; an unknown outcome is not a capture.
     */
    public function captureHold(string $reference, int $amountMinor): void;
}
```

- `HoldAdjustment` is an enum: `Ok | NeedsReauthorization`.
- Releasing a hold is the existing `SupportsPaymentIntents::voidIntent()`; no new verb.
- **Express eligibility** = `$method->supportsExpress() && $driver instanceof SupportsPaymentHolds
  && $method->isAvailable($cart)`. A gateway that cannot hold cannot be express; the squeeze
  page's "not charged until you confirm" promise is only honest with a hold.

### B. Stripe implementation

- `createHold`: PaymentIntent with `capture_method: 'manual'` and
  `payment_method_options.card.request_incremental_authorization: 'if_available'`.
- `adjustHold`: new total <= authorised returns `Ok` with no API call (under-capture is handled
  by `amount_to_capture` at capture time). New total above authorised calls
  `incrementAuthorization` when the confirmed intent reports
  `incremental_authorization_supported`, else returns `NeedsReauthorization`. Pre-capture, an
  increment appears as a second pending statement entry; after capture the pending entries
  collapse into one final charge.
- `captureHold`: PI capture with `amount_to_capture`.
- **Requires-capture is in-flight, never success.** Manual capture makes the gateway emit an
  authorisation event (Stripe: `payment_intent.amount_capturable_updated`) the moment the hold
  confirms. The reconcile layer and webhook handlers treat requires-capture as in-flight; only
  a captured/succeeded outcome completes a session. The 0002 late-success guard adapts to
  flavour: a stray *uncaptured* hold is released via `voidIntent`; `refundIntent` is only for
  an intent that actually captured.
- **Intent flavours.** Cart-scoped intent reuse (`getCartIntentId` / the `active()` relation
  scope) becomes flavour-keyed: `standard` vs `hold`, a new column on `stripe_payment_intents`.
  The standard card flow never resumes an express hold and express never resumes an
  automatic-capture intent. The relinquish and dead-intent rules apply within each flavour.

### C. Express region & the wallet sheet

- `ExpressWallets.vue` drops its hardcoded buttons: it filters the projected `paymentMethods`
  for express-eligible entries and renders each method's `expressComponent()` through the same
  component registry as the payment tabs ([[0009-frontend-element-extension]]). No eligible
  methods: the region and its OR divider unrender. A standalone gateway package registers a
  method plus a self-registering chunk and appears beside Stripe's with zero core change.
- Stripe ships `expressComponent() = 'stripe-express'`, a first-party `payments/StripeExpress.vue`
  mounting Stripe's Express Checkout Element. The wallets inside it are Stripe dashboard config.
- Sheet lifecycle, all against existing session endpoints (the server is the source of truth for
  every number the sheet displays):
  1. Mount with the current payable total. Fulfilment already chosen as collect: the sheet
     requests no shipping. Otherwise it collects shipping address and rates.
  2. `shippingaddresschange` (wallets share an anonymised address pre-confirm: city, postcode,
     country): mid-sheet lookups are **non-persisting quotes**. A new session-scoped POST
     `shipping-rates/quote` computes rates and totals for a candidate address without writing
     anything to the cart, so a dismissed sheet can never clobber an address the customer
     already entered on the checkout page. `shippingratechange` updates the sheet amount from
     the same quote data, still without persisting.
  3. `onConfirm` (full address, billing address, email, payer name now available): persist in
     one pass: `contact.store`, the full `shipping-address.store`, `billing-address.store`
     (the wallet's billing, not a copy of delivery), and `shipping-option.store` for the rate
     chosen in the sheet.
  4. Then, still inside `onConfirm`, POST the existing `payment-intent` endpoint with
     `mode: 'hold'` (the deferred-intent flow). The server routes to `createHold` only when the
     active method's driver implements `SupportsPaymentHolds`, otherwise 422. Creating the hold
     here, after the writes above, means the authorised amount is the final sheet total: a hold
     minted at button click would authorise the pre-shipping amount, because client-side
     confirmation charges the intent's amount, not the element's display amount. It also means
     a dismissed sheet leaves no orphan intent. Confirm the hold client-side (no redirect).
  5. Navigate to the confirm page. The server never trusts the client's "authorised" claim
     (see §D guard).
- Wallet-shared data lands as ordinary session state, so the squeeze page is pure projection.
  Element-bag fields ([[0011-address-lookup-and-element-bag]]: order reference, delivery notes)
  prefill where the wallet supplied an equivalent and are asked for where not.

### D. The confirm (squeeze) page

- Two new routes in the package group, UUID-constrained and ownership-gated like every
  sibling: GET `/checkout/{session}/confirm` (named `lunar.checkout.confirm`) and POST
  `/checkout/{session}/shipping-rates/quote` (§C's non-persisting mid-sheet rate quote,
  throttled like the lookup routes).
- **Render guard**: session `Open`, hold-flavoured `payment_intent_ref` present, and the gateway
  reports the intent as requires-capture via `fetchIntent`. Any guard failure redirects to the
  normal `show()`; the page is unreachable without a live verified hold.
- New `ExpressConfirm.vue` page in the same Inertia bundle, sharing `useCheckout` and the
  summary components, laid out per the design: review groups (contact / deliver-to or
  collect-from / shipping method / payment), a "few details to finish" group, a sticky confirm
  bar ("Card authorised, not charged until you confirm").
- The projection gains a `hold` block: `{ amountAuthorised, walletLabel }`, the wallet label
  read from the gateway's payment-method details for the "authorised with Apple Pay" copy.
- **Edits reuse the existing write endpoints unchanged** (contact, addresses, shipping option,
  fulfilment switch, element bag, discount codes: the design keeps the code box in the order
  summary). Every write already re-projects totals; the sticky bar tracks them live. Switching
  to collect or applying a discount drops the total, absorbed by under-capture; removing a
  discount raises it and rides the §E adjustment path like any other edit.

### E. Confirm & pay: the hold capture path

Hold adjustment happens **once, at the pay boundary**, never per edit (per-edit increments
would stack a pending statement entry per edit and burn fallible gateway calls). `pay` for a
session whose intent is hold-flavoured:

1. Total above authorised: `adjustHold`, BEFORE the pin (where the standard flow syncs its
   intent amount; a confirmed hold's amount cannot be edited, so holds adjust instead of
   syncing). `NeedsReauthorization` is a structured 422 with the session still `Open`:
   nothing to unpin, the UI opens the re-open-wallet panel.
2. Fingerprint pin, exactly as the standard flow (the squeeze page echoes the fingerprint it
   rendered; any drift is the existing `fingerprint_mismatch` 422). `Open ->
   PaymentProcessing`; the client is sent to the existing `processing` URL.
3. **Capture lives in the reconcile completion path**, not inline in `pay`: the processing
   landing (and the sweep, and the webhook) all funnel into the same completion code, which
   for a hold-mode session captures via `captureHold(session amount_total)` immediately
   before completing. One idempotent place means a crash between pin and capture recovers
   exactly like the customer path. Note the pre-existing semantic this must not break: the
   reconcile layer already completes on `RequiresCapture` *without capturing* to support a
   store-wide manual-capture policy (capture later from the panel against the order
   transaction). The two are distinguished by the session's intent mode (`hold` recorded in
   session meta at intent creation): hold-mode captures then completes; policy-mode keeps
   its existing behaviour.
4. Capture throws (hold expired, issuer decline at capture): the failed-completion path
   reopens the session with a structured error, and the UI shows the re-open-wallet panel.
5. Flavour-aware money-back rules in the same completion code: a hold that cannot complete
   is `voidIntent`-ed while uncaptured, refunded only once captured. Customer-initiated
   release of a pinned hold-mode session reopens it with the hold intact (the hold is not
   money moved; the standard flow's capture-or-refund treatment of `RequiresCapture` does
   not apply to it).

**Re-open wallet** (payment change, or the reauth fallback): the client POSTs `payment-intent`
with `{ mode: 'hold', renew: true }`; the server voids the old hold, mints a fresh one, the
sheet reconfirms, and the customer lands back on the squeeze page. The design copy ("your
current hold is released automatically, you won't be charged twice") is literally
`voidIntent` before `createHold`.

**Abandonment** needs nothing new: session expiry already voids advisory intents (0010 §E/§F);
holds die with the session.

### F. Cart-page entry

The package hands the host two pieces:

1. A server helper, `Express::projection(Cart $cart)`: the express-eligible methods plus client
   config, the same shape as the checkout projection's `paymentMethods`. The host spreads it
   into its cart page props. It also carries `payable` (any registered method can serve this
   basket) and `unavailable` (the reasons withdrawn methods gave, [[0002-payment-methods-and-driver]] §B),
   so a host can close its checkout button and say why before the customer reaches an empty
   payment region.
2. A standalone `express.js` entry exporting `mountExpress(el, { methods, startUrl, amount })`,
   reusing the same `expressComponent` chunks, for host pages that are not the checkout bundle.

Session minting stays lazy (a session is a mutation, never minted on a bare GET, spec 0004).
On wallet click from the cart: resolve the sheet within the wallet's gesture window immediately
(shipping flags plus a placeholder rate) while POSTing the existing `/checkout` start action in
parallel; `shippingaddresschange` awaits the minted session UUID and then follows §C verbatim.
A failed mint or dismissed sheet returns the button to idle: no orphan session beyond the
normal expiry sweep. From `onConfirm` onward the cart entry and checkout entry are one flow.

### G. Fulfilment interplay

Both directions are supported (decision: option C):

- Fulfilment pre-chosen as click & collect: the sheet skips shipping collection entirely; the
  squeeze page shows the "Collect from" group. The wallet's billing address is kept as billing.
- Delivery flow: the sheet collects address and rate; the squeeze page allows switching to
  collect afterwards (total drops, under-capture handles it) or editing the address/rate
  (total may rise, §E step 2 handles it).

## Operational notes

- Apple Pay requires the storefront domain registered with the gateway per environment
  (Stripe: dashboard payment-method domains). A rollout step, not code.
- A card authorisation is valid for roughly 7 days (network-dependent). Session expiry is far
  inside that today; if expiry policy ever lengthens, capture-time failure is already handled
  (§E step 4), but the expiry sweep should stay well inside auth validity.
- Stock is not reserved by a hold. A line selling out between wallet and Confirm & pay is
  caught by the pay boundary's existing order-creation validation, like the standard flow.

## Testing

- **Checkout package**: squeeze route guard (no hold / wrong flavour / not requires-capture
  redirects to show); hold pay path (pin, adjust, capture, `PaymentProcessing`);
  `NeedsReauthorization` 422 unpins; capture failure releases the pin; `renew: true` voids
  before minting; expiry voids holds; `mode: 'hold'` refused with 422 for a driver without the
  capability; the rate quote persists nothing (address and selected option unchanged after a
  quote); an authorisation event for a session not in `PaymentProcessing` releases the hold
  (void, not refund) and never completes the session. The contract-test fixture's fake gateway grows `SupportsPaymentHolds` so the
  seam is exercised end-to-end, per the 0002 acceptance pattern.
- **Stripe package**: MockClient fixtures for a manual-capture PI, both
  `incremental_authorization_supported` variants, capture and increment endpoints. Flavour
  keying: standard flow never resumes a hold and vice versa. `adjustHold` matrix: lower total
  is `Ok` with no API call; higher with support increments; higher without support returns
  `NeedsReauthorization`.
- **Host (Edwardes)**: cart props carry the express projection; the confirmation page needs no
  change (squeeze completion lands on it through the existing completed-session handover).
- **Manual plan**: wallet sheets cannot run headless; a device pass is part of the rollout,
  following the EDW2-138 manual test plan format.

## Out of scope

- Product-page express entry (buy-single-item semantics; needs its own cart decision).
- Saved/tokenized wallets beyond the sheet flow (deferred in 0002, still deferred).
- Per-edit hold adjustment or partial-capture UI (deliberately rejected, §E).
- Any wallet-specific code in core checkout (which wallets appear inside a gateway's element
  is that gateway's configuration).

## Open questions

- Ordering when several gateways are express-eligible at once (also open in 0002 §Open
  questions; v1 renders registration order).
- Whether `express.js` should also power a mini-cart / drawer placement later.

## References

- [[0002-payment-methods-and-driver]] §A/§C (method interface, express seam, submit seam)
- [[0004-checkout-session]] §C (state machine, completion paths)
- [[0009-frontend-element-extension]] (self-registering component chunks)
- [[0010-cart-session-reconciliation]] §E/§F (advisory intents, expiry voiding, pin)
- [[0011-address-lookup-and-element-bag]] §D/§G (element bag, OrderDetails fields)
