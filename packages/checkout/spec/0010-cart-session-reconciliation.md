# 0010 — Checkout session: backend-neutral data model, driver surface & pay-time integrity

- Status: draft
- Author: Alec Ritson
- Created: 2026-06-11
- TODO item: "Checkout session — make the session backend-neutral; keep it in sync with a live cart; guarantee the charged amount matches the cart at pay time; define the driver store/read surface, the element data bag, and lifecycle events"

## Problem

[[0004-checkout-session]] introduced the session as "driver-based" but the first
implementation baked Lunar in: a `cart_id` FK to `lunar_carts`, `channel_id` /
`customer_id` FKs, and a `cart()` relation. A non-Lunar driver (a Statamic
basket, a headless cart) may have no `lunar_carts` row — and its cart may not be
an Eloquent model at all. The session table must hold **nothing Lunar-specific**.

It also left the integrity model underspecified. The cart legitimately changes
throughout checkout — shipping, tax, a coupon, a **charity donation or gift-wrap
fee** (a price-affecting element) — and the customer should see those reflected,
not be bounced out. The **one** thing that must never happen: the customer clicks
pay and is charged an amount that does not match what is actually in their cart.

That forces three linked questions this spec answers:

1. **What does the session store vs derive?** Duplicating the cart's
   address/shipping/lines drifts; storing nothing can't pin the charged amount.
2. **How does the session stay correct as the cart changes**, while guaranteeing
   the charged amount equals the live cart at the moment of payment?
3. **How do custom elements store their data** (including price-affecting ones),
   and how do consumers hook the base flow?

## Proposal

One rule for the data model: **store what you cannot derive; derive everything
else through the driver.** One rule for integrity: **the session tracks the live
cart while `Open`; the charged amount is locked and confirmed against the live
cart only at the pay boundary.** Reconciliation, store, and read are all
driver-mediated; the session holds an opaque `cart_reference` and never touches a
backend cart directly. The default `LunarCheckoutDriver` implements the surface
over Lunar's `Cart`; a non-Lunar driver brings its own.

### A. What the session stores vs derives

**Stored** (owned, non-derivable — every column backend-neutral, no Lunar FK):

| Column | Why stored (not derived) |
|--------|--------------------------|
| `uuid` | public capability token / identity |
| `status` | lifecycle state machine ([[0004-checkout-session]] §C) |
| `currency_code`, `locale`, `channel_handle` | **pinned context** — strings, not FKs; `channel_handle` replaces the `channel_id` FK |
| `amount_subtotal`, `amount_total` | the current payable — a **live reflection of the cart while `Open`**, **frozen at the pay boundary** (§D/§E). Minor units — canonical to the Lunar currency's `decimal_places`; the gateway driver owns the translation to/from gateway units in **both** directions — first-class, not buried in `meta` |
| `cart_fingerprint` | the **driver-owned** integrity fingerprint (§D) — the **confirmation token** + drift reference (§D/§E) |
| `cart_reference` | **driver-opaque** handle to the source cart (string — not an FK; the source may be non-Lunar / non-Eloquent) |
| `customer_reference`, `customer_email` | driver-opaque customer handle + pinned contact for guest resume / notifications (replace the `customer_id` FK) |
| `payment_intent_ref` | gateway-agnostic in-flight intent handle ([[0004-checkout-session]] §A/§E) — written when the intent is **created** (nullable while advisory, §E); rotated void-first on retry. Unique (nullable) so a webhook resolves to exactly one session |
| `client_reference_id` | merchant correlation id |
| `success_url`, `cancel_url` | hosted return URLs |
| `order_reference` | the produced order — **driver-opaque** string (not a morph: a non-Lunar driver's order may not be an Eloquent model, the same reasoning that made `cart_reference` a string). The Lunar driver stores the order id; the model resolves through the driver |
| `element_data` | **element bag** (§C) — json (`jsonb` where the platform supports it) keyed by element handle; non-derivable custom-element data |
| `metadata`, `meta` | merchant-supplied / Lunar-internal extras |
| `active_cart_reference` | **one-active-session enforcement** (§F.2) — mirrors `cart_reference` while the session is `Open`/`PaymentProcessing`; set to `NULL` in the **same UPDATE** as any terminal transition; plain **unique** index (multiple `NULL`s allowed on MySQL/Postgres/SQLite alike) |
| `payment_processing_at` | set in the §E pin UPDATE — the reconciliation sweep's age anchor (`updated_at` moves on any write and would re-arm itself) |
| `reconciliation_attempts` | unsigned tinyint, default 0 — the §F bounded-attempt counter |
| `pruned_at` | retention-prune idempotency marker (§F / [[0004-checkout-session]] migration impact) |
| `expires_at`, `completed_at`, `cancelled_at`, timestamps | lifecycle |

**Indexes:** unique `uuid`; `(cart_reference, status)`; unique `active_cart_reference`;
`(status, expires_at)`; `(status, payment_processing_at)`; unique (nullable)
`payment_intent_ref` — webhook→session resolution must be unambiguous; `(status,
pruned_at)` for the prune scan.

**Derived** through the driver (never stored, never duplicated): **line items,
shipping address, billing address, shipping option(s)** — anything in the backend
cart. Read on demand for hosted render / resume (§B read verbs).

### B. The driver surface

`CheckoutDriver` ([[0004-checkout-session]]) grows from two verbs to the full
checkout surface. Lifecycle + integrity (read-side):

| Verb | Responsibility |
|------|----------------|
| `createSession($source): CheckoutSession` | ingest a source cart → session ([[0004-checkout-session]] §B) |
| `complete(CheckoutSession): mixed` | finalise → order ([[0004-checkout-session]] §E); MUST be **idempotent keyed on the session `uuid`** — create-or-fetch (§E.2) |
| `snapshot(CheckoutSession): CartSnapshot` | re-read the live source cart into a neutral DTO — the re-sync input. Full shape: `amountSubtotal`, `amountTotal`, `currencyCode`, `channelHandle`, `fingerprint`, `hasAppliedDiscount` (bool), `couponCode` (`?string`). `currencyCode`/`channelHandle` feed the §D divergence check — a hash change alone cannot distinguish "price changed → absorb" from "currency changed → invalidate"; the discount fields let the discount element read state |
| `fingerprint(CheckoutSession): string` | the **driver-owned** integrity fingerprint of the live cart (§D) |
| `assertReadyForPayment(CheckoutSession, string $confirmedFingerprint): void` | the pay-boundary gate (§E); throws on a diverged pinned context, a changed-since-confirmed cart, or an unorderable cart |

Store verbs — the universal, price/identity-affecting pieces (typed; a custom
element does **not** use these — §C). Each re-syncs the session afterward:

| Verb | Lunar driver behaviour |
|------|------------------------|
| `storeShippingAddress(CheckoutSession, array): CartSnapshot` | `cart->setShippingAddress()` → recalc → re-snapshot |
| `storeBillingAddress(CheckoutSession, array): CartSnapshot` | `cart->setBillingAddress()` → re-snapshot |
| `storeContact(CheckoutSession, array): CartSnapshot` | guest email/phone capture — writes `customer_email` + contact fields onto the cart addresses (the contact element's write path) |
| `setShippingOption(CheckoutSession, string): CartSnapshot` | `cart->setShippingOption()` → recalc → re-snapshot |
| `applyCoupon(CheckoutSession, string): CartSnapshot` / `removeCoupon(CheckoutSession): CartSnapshot` | mutate the cart coupon → recalc → re-snapshot (a price-affecting cart write, like shipping). `removeCoupon` takes no code; the context verb `applyCoupon(?string)` maps `null` → `removeCoupon` |
| `associateCustomer(CheckoutSession $session, string $customerReference, ?string $email = null): CartSnapshot` | associate user/customer; hydrate `customer_reference` + `customer_email`. Triggered by a **host-app login listener** — it has no element/endpoint caller |

Read verbs — surface the derived data for hosted render / resume:
`getShippingAddress` / `getBillingAddress` (`?CheckoutAddress`), `getShippingOptions`,
`getLines` (neutral shapes), `getSelectedShippingOption(CheckoutSession): ?string`,
`getCoupon(CheckoutSession): ?string`. `CartSnapshot` and `CheckoutAddress` are
neutral DTOs under the checkout package's `src/DataObjects/`
(`Lunar\Checkout\DataObjects\…`). `CheckoutAddress` is defined once: `firstName`,
`lastName`, `companyName`, `line1`, `line2`, `line3`, `city`, `state`, `postcode`,
`countryCode`, `phone`, `email` — all nullable strings except `countryCode`.

**Contract stability.** The contract stays **one interface**, but third-party
drivers MUST extend the shipped `AbstractCheckoutDriver` base class; future verb
additions land there with default implementations or explicit `…NotSupported`
exceptions, so interface growth does not break drivers.

### C. Custom elements & the element bag

Two kinds of custom element:

- **Price-neutral** (gift message, consent, age-gate). The data is non-derivable
  and does not affect the cart total → the **session owns it** in the element bag.
  The element's `store(array $data): void` (`CapturesData`) validates, then
  reaches the bag **through the context** — `$context->putElementData($handle,
  $data)` / `$context->getElementData($handle)` — never the session model
  directly (context mediation, [[0001-core-element-model]] §C/§D). The driver
  never sees it. Bag writes — like **all persisted session writes** — serialize
  on the **session row** (`lockForUpdate` on `checkout_sessions`, the
  backend-neutral anchor): two concurrent bag writes must not lose updates
  (read-modify-write of the whole json blob under the lock, or an atomic
  JSON-path update). Bag-backed custom elements therefore **require the session
  flow**; the session-less embedded flow has no bag.
- **Price-affecting** (charity donation, gift-wrap fee). These **do** move the
  total, so they cannot live only in the bag — the element mutates the cart
  through the driver (adds a cart line / fee), and the change flows through the §D
  live-sync exactly like shipping or a coupon. Any non-derivable *config* (e.g. a
  chosen donation amount the UI should re-display) may also be kept in the bag.
  **A price-affecting element while `Open` updates the session total; it never
  invalidates the session** (§D).

### D. The integrity model — sync while Open, pin at pay

The #1 invariant: **when the customer clicks pay, the amount charged equals the
live cart total for exactly what is in their cart, and the customer has confirmed
that amount.** Everything else serves it.

While `Open` the session is **permissive**: it mirrors the live cart. Any
price-affecting change made during checkout — shipping, tax, coupon, a donation or
fee (§C), even an out-of-band line change from another tab — is **absorbed**: a
store verb persists a re-sync into `amount_subtotal` / `amount_total` /
`cart_fingerprint`, and an out-of-band change surfaces on the next render's live
snapshot (reads never write — below). The customer always sees the current total;
nothing is rejected and the session is never invalidated for a price change while
`Open` (currency/channel divergence excepted — below). The stored amounts are a
live reflection, **not** a frozen contract.

`CheckoutSession::hasDrifted(): bool` (stored `cart_fingerprint` vs
`CheckoutDriver::fingerprint($session)`) is therefore not an error condition — it
is a render-time hint ("your total changed, here is the new one") and the input to
the pay-boundary gate.

**The fingerprint MUST change whenever the payable total changes** — lines,
quantities, currency, coupon, fees, shipping cost, tax: any input to the amount,
however it was mutated. A fingerprint that excludes a total-affecting input is
forbidden: the first draft excluded address/shipping on the grounds that those
flow through re-syncing store verbs — but the cart API is public surface, an
out-of-band `setShippingAddress()` from another tab changes tax/shipping (and so
the total) without touching a store verb, and the §E gate would then pass on a
stale token and charge a total the customer never saw.

The integrity fingerprint is a **new, driver-owned construction** — it is not
core's draft-order dedupe fingerprint. `LunarCheckoutDriver::fingerprint()`
computes **HMAC-SHA256** (keyed with the app key) over a structured
(JSON-encoded, delimiter-safe) payload of: line content (purchasable type, id,
quantity, line subtotal), shipping address identity (normalized fields), billing
address identity, the selected shipping option identifier, the coupon code,
`amount_total` (minor units), and `currency_code` — so the confirmation token the
customer submits at pay *is* a commitment to an exact amount. It is **not**
resolved through the swappable `GeneratesFingerprint` container binding, and the
driver never calls `Cart::checkFingerprint()` raw — that method satisfies a
different (dedupe) concern, hashes a narrower input set (no `amount_total`, no
shipping/tax), and throws on mismatch; the checkout gate translates a mismatch
into the `409` + re-sync flow itself (§E).

Two deliberate scope decisions:

- **Customer identity (`user_id` / customer association) is excluded.** A guest
  logging in mid-checkout is payment-neutral and must not flip the fingerprint —
  at the completion boundary (§E.2) a flipped fingerprint would refund a
  legitimate captured payment.
- **Address + shipping-option identity are included** (strictest-correct): an
  out-of-band *same-total* address swap must not ship goods to an address the
  confirmation token never covered. This also closes total-neutral drift in
  general.

Two bounds on the permissiveness:

- **Currency or channel divergence is not absorbed.** `currency_code` /
  `channel_handle` are pinned context — the price universe of the session. A
  re-sync whose `CartSnapshot` (`currencyCode` / `channelHandle`, §B — a hash
  change alone cannot distinguish "price changed → absorb" from "currency
  changed → invalidate") no longer matches the pinned values does not update
  amounts; it **invalidates** the session (`InvalidateCheckoutSession` →
  `Cancelled`, void-first, reason in `meta`). The same check is gate step 0
  (§E) — reads never write, so divergence-before-render must be caught there.
  The customer starts a new session pinned to the new context — a GBP session
  carrying EUR-derived amounts is incoherent.
- **Reads never write.** A render (`GET /checkout/{uuid}`, the hosted page)
  computes the live snapshot + fingerprint through the driver and serves *that* —
  including the confirmation token — persisting nothing; `hasDrifted()` is computed
  on demand. The **persisted** `amount_*` / `cart_fingerprint` update only on
  store verbs (§B) and at the pay-boundary pin (§E). GETs stay idempotent — no
  write race between concurrent renders, no side-effecting read under Octane —
  and while `Open` the render path never depends on the stored values; they are
  the last write-time sync plus, after the gate, the pinned contract.

**Every persisted re-sync is a single-statement guarded write** (normative):
`UPDATE checkout_sessions SET amount_total = ?, amount_subtotal = ?,
cart_fingerprint = ? WHERE id = ? AND status = 'open'`. Zero rows affected means
the §E freeze won the race — the sync is **dropped**, never applied over a pinned
contract: a straggler store-verb re-sync racing the pay gate must not clobber the
pinned amount/fingerprint. (The underlying cart mutation is then caught by §F
invalidation / the §E.2 re-verify, which already handle it.)

The amounts crystallise into a frozen contract **only at the pay boundary** (§E).

### E. The pay-boundary confirmation gate (the guarantee)

Enforced server-side at the `Open → PaymentProcessing` transition, not at any
page-load prompt. Each render carries the current `cart_fingerprint` as a
**confirmation token** — the customer is agreeing to the total that token
represents. On pay the client submits that token and
`CheckoutDriver::assertReadyForPayment($session, $confirmedFingerprint)` MUST
pass, in order:

0. **Pinned-context match** — the live cart's `currency_code` / `channel_handle`
   equal the session's pinned values. A mismatch is never absorbed and never
   retried: **invalidate** the session (void-first, §F) and reject (`409`).
   Renders may *surface* the divergence (a DTO flag) but never persist or void —
   only this gate step acts on it.
1. **Recompute the live cart** (total + fingerprint).
2. **Confirmed-amount match** — the live fingerprint equals the token the customer
   confirmed. If it differs, the cart changed between render and pay: **reject**
   (`409`), re-sync the session, re-render the new total with a fresh token, and
   make the customer confirm again. They are never charged an amount they did not
   just see.
3. **Gate 2 — cart still orderable, elements still valid** —
   `ValidateCartForOrderCreation` (stock, min-qty, …) plus element re-validation
   / `canCreateOrder`. Gate 2 runs here at the pay boundary **and again inside
   `complete()`** on the async path (§E.2).

Only when all pass does the session **pin** — `amount_total` to the live total
*and* `cart_fingerprint` to the live fingerprint — and transition to
`PaymentProcessing`. The pinned pair is the frozen contract: the amount sent to
the gateway is the pinned total, and the pinned fingerprint is what the
completion boundary (§E.2) re-verifies before an order exists. The pin is **one
guarded statement** — status, `amount_total`, `amount_subtotal`,
`cart_fingerprint`, `payment_intent_ref`, and `payment_processing_at` together,
`WHERE id = ? AND status = 'open' AND (expires_at IS NULL OR expires_at >
now())`. The expiry predicate lives **inside** the atomic statement, never in a
pre-check that could pass and then race the expiry sweep. Zero rows affected
means the submission lost (a concurrent pay, an expiry, a supersede): re-read
and re-render — two concurrent pay submissions race to exactly one
`PaymentProcessing` entry. This is an instance of the guarded-transition rule
([[0004-checkout-session]] §C): the spatie machine answers *legality*; the
guarded UPDATE answers *concurrency*, at **every** transition call-site. The
Lunar driver implements the checks with the **driver-owned fingerprint** (§D —
never `Cart::checkFingerprint()`) + `ValidateCartForOrderCreation`. This is the
contract point the payment layer ([[0002-payment-methods-and-driver]]) calls
first; 0002 wires it into the payment entry point.

**Store verbs on the pay path carry the token too.** A client that writes a
fingerprint input moments before paying (the default flow copies the delivery
address to billing, then pins) is handed the post-write fingerprint so its pin
can succeed. That hand-back would otherwise launder any change made elsewhere
into a matching pin, so such a write MUST also accept the token the customer is
looking at and refuse a stale one (`422`, the step 2 copy) before writing
anything. The client then re-syncs and re-renders, exactly as after a step 2
rejection.

**Intent timing & ordering.** Default posture: the intent is created **at the
pay boundary**, after the pin. A gateway that needs a pre-confirmation intent (a
mounted payment UI) may **opt in** to creating it while `Open`, at the
then-current synced total — that intent is *advisory* until the gate, and
`payment_intent_ref` is written when the intent is **created** (§A). A retry
with a new intent **voids the previous intent first**, then rotates
`payment_intent_ref` in the same guarded write. At the gate, after pinning, the
gateway driver MUST set (or verify) the intent's amount + currency to the pinned
values **before** the client is told to confirm; confirming against an intent the
server has not re-pinned is forbidden. The driver's capture-side verify
([[0002-payment-methods-and-driver]] §E) remains the last line: a mismatch that
survives anyway (a gateway-side race) fails capture and resolves through §E.2 —
never a silent wrong charge.

This is why an `Open` re-sync can never cause a wrong charge: a store verb (or an
out-of-band change) re-syncing the cart only updates what the customer *sees*;
nothing authorises a charge except this gate, which always re-checks the live cart
against the exact amount the customer just confirmed.

### E.2 The completion boundary — re-verify, then order; no charge without an order

Lines, addresses, and shipping are derived from the **live** cart at completion
(§A), so `complete()` MUST prove the live cart is still the one the customer paid
for before an order is created:

1. **Re-verify, under a consistent read.** Inside the same transaction that
   creates the order, the driver recomputes the live fingerprint and requires it
   to equal the session's **pinned** fingerprint. The Lunar driver reads the
   cart row **and** its lines/addresses `FOR UPDATE` **once**, computes the
   verification fingerprint **from that loaded set**, and builds the order
   **from the same set** — never re-querying between the verify and the order
   insert. (A `lockForUpdate()` on the carts row alone serializes nothing:
   core's public cart API takes no locks, and Postgres READ COMMITTED gives
   every statement its own snapshot.) The session-row lock (§C) serializes
   checkout's own writes; the consistent read closes the out-of-band writer.
   Gate 2 (element re-validation + `canCreateOrder`, §E) runs again here on the
   async path.
2. **Match → order.** Create the order from the consistent-read set; the
   guarded `→ Completed` write sets `order_reference` **in the same statement**,
   after the order exists. The transition is the same atomic guarded write as §E
   (`WHERE status IN (open, payment_processing)`), which also covers the
   synchronous `Open → Completed` path that has no `payment_intent_ref` to key
   on: a duplicate callback, a webhook+redirect race, or a double-submitted
   synchronous completion finds `Completed` and returns the existing order —
   never a second one. A non-Lunar driver cannot share a transaction with a
   remote order system, which is why `CheckoutDriver::complete()` MUST be
   **idempotent keyed on the session `uuid`** (create-or-fetch, §B).
3. **Mismatch or failure → the refund invariant.** If the live cart changed during
   the gateway window, or order creation fails (e.g. stock gone under the
   `ValidateCartForOrderCreation` re-check):
   - **Payment not captured** — abort/void the intent via the gateway driver;
     only once the void is **gateway-confirmed** does the session transition
     `→ Open` (re-synced, `CheckoutCompletionFailed`); the customer re-confirms
     the new total. The unknown-outcome guard (§F) applies uniformly here too: a
     void that fails or whose outcome is unknown keeps the session in
     `PaymentProcessing` for the §F sweep.
   - **Payment captured** — **no charge survives without an order**: the gateway
     driver refunds the captured amount. The refund request carries an
     **idempotency key derived from the intent ref**, and the session moves
     `→ Open` only after the refund is **gateway-confirmed** (re-synced; the
     customer is informed and may retry). Refund failed or outcome unknown ⇒ the
     session **stays `PaymentProcessing`**; the §F sweep retries; exhausted
     attempts fire `CheckoutSessionReconciliationStalled`.
     `CheckoutCompletionFailed` fires with a `reason` distinguishing
     **refunded / refund-pending / refund-failed**, carrying the refund
     reference when one exists. A non-Lunar driver may substitute its own
     remediation (a backorder, a manual-review hold) but MUST NOT silently
     retain a charge that produced no order.

**`→ Open` return-path hygiene** (normative — every return path, here and in
§F): the guarded UPDATE that returns a session to `Open` **clears
`payment_intent_ref` and `payment_processing_at` in the same statement** and
**re-arms `expires_at`** with a configurable grace window (default 30 minutes) —
a pre-payment `expires_at` must not instantly expire a session the customer is
actively retrying.

**Misdirected gateway events** (normative): a gateway success event that
resolves to a session **not** in `PaymentProcessing`, or to an intent that is
not the session's current `payment_intent_ref` (rotated or superseded), is
**refunded — never completed**: record the refund reference and fire
`CheckoutCompletionFailed` with a distinguishing `reason`.

**Synchronous path** (`requiresIntent() === false`; **forced** whenever
`amount_total == 0`, regardless of `requiresIntent()`): not a gate bypass. The
**same** §E gate — confirmation-token verify, gate step 0, pin — runs **inside
`complete()`**, in the order-creation transaction, under the consistent-read
protocol above. One transaction: verify → pin → Gate 2 → order → guarded
`Open → Completed` ([[0004-checkout-session]] §C).

§E proves the customer confirmed the amount; §E.2 proves the order contains
exactly what that amount paid for. Together they are the invariant.

### F. PaymentProcessing — frozen; out-of-band change invalidates

`PaymentProcessing` is the **only** state where a change is rejected rather than
absorbed: the gateway is authorising the pinned amount and it cannot move. An
out-of-band cart change detected here (a fingerprint check, or the §E.2
re-verify) **invalidates** the session — but invalidation is **void-first**,
because `Cancelled` is terminal and a charge landing after it would have no
remediation path:

1. The gateway driver attempts to **abort the in-flight intent**.
2. **Voided** → transition `→ Cancelled` (a **stable machine reason code** in
   `meta` — reason codes are machine strings, translated at render via
   `lunar-checkout::checkout.*`, never English prose; `CheckoutSessionInvalidated`).
   The customer starts a new session, which
   reflects the new cart. Never re-pinned mid-flight, never a double-charge
   source.
3. **Not voidable** (the charge already succeeded, or the gateway cannot cancel)
   → the session **stays `PaymentProcessing`** and resolves through §E.2:
   complete if the live cart still matches the pinned fingerprint, refund
   otherwise. A session is never moved to a terminal state while its intent's
   outcome is unknown.

No items are lost; they remain in the cart and seed the new session.

**Void-first applies to every terminalizing transition from `Open`** — a
supersede (§F.2), an expiry, an explicit cancel — not only to
`PaymentProcessing` invalidation: an advisory `payment_intent_ref`, when one
exists, is voided **before** the terminal transition, and an unabortable /
unknown-outcome void blocks the terminal transition exactly as above.

Every intent operation in §E.2/§F — fetch, void, refund — goes through the new
core gateway capability interface `Lunar\Core\Contracts\SupportsPaymentIntents`
(`fetchIntent(string $ref)`, `voidIntent(string $ref)`, `refundIntent(string
$ref, int $amount, string $idempotencyKey)`), `instanceof`-checked on the
gateway driver ([[0002-payment-methods-and-driver]] carries the seam detail). A
gateway that does not implement it cannot void — its session stays
`PaymentProcessing` and resolves through the stall protocol below.

**`PaymentProcessing` is bounded.** A stale `PaymentProcessing` session is never
blindly expired (the payment may have succeeded) — and never left forever either.
The scheduled command that expires stale `Open` sessions also picks
`PaymentProcessing` sessions older than a configurable window (default 1h —
aged on `payment_processing_at`, §A) and **forces reconciliation** through the
gateway driver: `fetchIntent` → §E.2 (complete / refund) or void → `Open`
(return-path hygiene, §E.2). Each run increments `reconciliation_attempts`; a
still-pending intent on a slow method (bank transfer, voucher) **reschedules
without consuming an attempt**, up to a method-declared deadline. After the
maximum attempts the session is **stalled**:
`CheckoutSessionReconciliationStalled` fires **once** (never per-run noise),
and the session drops to a **low-frequency retry tier** — not silence. The
sanctioned exits from a stall are the operator command
`lunar:checkout:reconcile {uuid} --resolve=complete|refund|cancel` and a
supersede-after-stall gated on a **confirmed void** (§F.2); `createSession`
against a stalled session's cart returns `409` with a `stalled` reason code
until one of those exits applies. Retention pruning anchors on the terminal
timestamps (`completed_at` / `cancelled_at` / `expires_at`); a stalled session
is pruned only after it reaches a terminal state through a sanctioned exit.

### F.2 One active session per cart

`cart_reference` 1:N sessions is per-*attempt* history, not per-attempt
concurrency:

- `createSession` for a source cart that already has an **`Open`** session
  **supersedes** it — the prior session transitions `→ Cancelled` and
  `CheckoutSessionSuperseded` fires. Two parallel `Open` sessions would both pass
  the §E gate (both fingerprints match the same live cart) and double-charge.
  Supersede is **void-first** (§F): an advisory `payment_intent_ref` on the
  superseded session is voided before its `→ Cancelled`.
- `createSession` while a sibling is in **`PaymentProcessing`** is **refused**
  (`409`): a charge for that cart is in flight, and minting a second payable
  session is the double-charge path. The §F sweep resolves the in-flight session
  on the happy path — but resolution is **not** guaranteed: a stalled session
  (§F) keeps the refusal in place, with a `stalled` reason code, until a
  sanctioned stall exit applies.

The mechanism is the `active_cart_reference` column (§A): it mirrors
`cart_reference` while the session is `Open`/`PaymentProcessing` and is set
`NULL` in the **same UPDATE** as any terminal transition, under a plain
**unique** index — multiple `NULL`s are allowed on MySQL, Postgres and SQLite
alike. (A *partial* unique index does not exist on MySQL, and a transaction
alone cannot prevent two concurrent first-creates: there is no row to lock.)
`createSession` runs: a **guarded supersede-cancel** of any prior `Open`
sibling (a single UPDATE; void-first per §F when an intent ref exists) →
INSERT → on a unique violation, re-read the sibling: it shows
`PaymentProcessing` → `409`; it shows `Open` (a lost create/create race) →
exactly **one** supersede-retry. A 0-rows supersede-cancel MUST NOT be read as
"no sibling" — the sibling may have been inserted after the UPDATE scanned;
re-check before the insert. Two concurrent create calls therefore cannot both
succeed.

### G. Events

Granular domain events on the base flow let consumers hook tax services,
analytics, fraud checks, and custom validation. Each carries the session plus the
relevant payload.

| Event | Fires when |
|---|---|
| `CheckoutSessionCreated` | session minted from a cart |
| `ShippingAddressStored` / `BillingAddressStored` | a store verb stored + re-synced |
| `ShippingOptionSet` | shipping option applied |
| `CouponApplied` / `CouponRemoved` | coupon store verb applied |
| `CustomerAssociated` | customer authenticated/associated |
| `CheckoutSessionResynced` | amounts re-pinned from the live cart while `Open` (covers element-data + price-affecting-element writes) |
| `CheckoutPaymentConfirmationFailed` | pay-boundary gate rejected a changed-since-confirmed cart |
| `CheckoutSessionInvalidated` | → `Cancelled` (incl. paying-window invalidation) |
| `CheckoutSessionCompleted` | order produced |
| `CheckoutSessionSuperseded` | a new session for the same cart cancelled a prior `Open` one (§F.2) |
| `CheckoutCompletionFailed` | the §E.2 completion re-verify or order creation failed, or a misdirected gateway success event was refunded (§E.2); carries a `reason` distinguishing **refunded / refund-pending / refund-failed** (stable machine strings) plus the refund reference when one exists |
| `CheckoutSessionReconciliationStalled` | bounded reconciliation exhausted its attempts on a `PaymentProcessing` session (§F) |

Spatie's state machine already fires transition events for terminal lifecycle
states (`Expired`, etc.); the table does not duplicate those.

### H. New surface (summary)

- **Columns:** `cart_reference`, `cart_fingerprint`, `channel_handle`,
  `customer_reference`, `element_data`, `order_reference` (replacing the
  `order_type`/`order_id` morph), `active_cart_reference` (nullable, unique —
  §F.2), `payment_processing_at`, `reconciliation_attempts`, `pruned_at` — and
  removal of the `cart_id` / `channel_id` / `customer_id` FKs and the `cart()`
  relation from the first implementation. The §A index set.
- **DTOs:** `CartSnapshot`, `CheckoutAddress` under the package's
  `src/DataObjects/` (`Lunar\Checkout\DataObjects\…`).
- **Driver surface:** the §B verbs added to `CheckoutDriver`, plus the mandatory
  `AbstractCheckoutDriver` base class (§B). New core gateway capability
  interface `Lunar\Core\Contracts\SupportsPaymentIntents` (§F).
- **Session API:** `hasDrifted()`, `putElementData()` / `getElementData()`.
- **Actions:** `SyncCheckoutSession` (guarded single-statement re-sync while
  `Open`, §D), `InvalidateCheckoutSession` (void-first → `Cancelled` + reason,
  §F), `ReconcileCheckoutSession` (bounded `PaymentProcessing` resolution, §F).
  DI'd, `execute()`-only. Operator command `lunar:checkout:reconcile {uuid}
  --resolve=complete|refund|cancel` — the sanctioned stall exit (§F).
- **Events:** the §G set.
- **No new state, no Lunar FKs.** `Open` covers the permissive working state;
  invalidation reuses `Cancelled`.

## Alternatives considered

- **Freeze the subtotal at creation, reject any drift.** Rejected: it bounces a
  customer out for legitimate, desired changes (donation, coupon, fee, shipping)
  and over-rotates on creation-time state. The real invariant is at the pay
  boundary, not at creation — so sync freely while `Open` and confirm at pay.
- **Store the collected address/shipping on the session.** Rejected: derivable
  from the driver, so a stored copy is a second source of truth that drifts.
- **Generic driver mediation verb** (`applyElementData`). Rejected: custom
  elements own their own storage (§C); the driver only needs typed verbs for the
  universal, price/identity pieces.
- **Polymorphic `cart` morph instead of `cart_reference`.** Rejected: a morph
  needs an Eloquent model with an id; non-Eloquent backends can't satisfy it.
- **Keep `channel_id` / `customer_id` FKs.** Rejected: Lunar-specific on a neutral
  table; `channel_handle` / `customer_reference` strings carry the same info.
- **Re-pin / charge silently when the cart changed under the customer.** Rejected:
  the customer must confirm the exact amount charged (§E), never be surprised.

## Migration impact

- `checkout_sessions`: drop `cart_id` / `channel_id` / `customer_id` FKs; add
  `cart_reference`, `cart_fingerprint`, `channel_handle`, `customer_reference`,
  `element_data`, `active_cart_reference`, `payment_processing_at`,
  `reconciliation_attempts`, `pruned_at`; replace the `order_type`/`order_id`
  morph with `order_reference`; the §A index set (unique `uuid`,
  `(cart_reference, status)`, unique `active_cart_reference`, `(status,
  expires_at)`, `(status, payment_processing_at)`, unique nullable
  `payment_intent_ref`, `(status, pruned_at)`). Folded into the (unshipped)
  create-table migration.
- `CheckoutSession` model loses `cart()` / `channel()` / `customer()` relations;
  gains `hasDrifted()`, `putElementData()`/`getElementData()`, the `element_data` cast.
- `CheckoutDriver` contract + `LunarCheckoutDriver` grow the §B verbs.
- `CreateCheckoutSession` pins `cart_reference` + `cart_fingerprint` +
  `channel_handle` + `customer_reference` at creation.
- New `SyncCheckoutSession` / `InvalidateCheckoutSession` / `ReconcileCheckoutSession`
  actions + contracts; `CartSnapshot` / `CheckoutAddress` DTOs; the §G events; the
  expiry command grows the bounded `PaymentProcessing` reconciliation sweep (§F).
  The command's `Open`-expiry path moves to the guarded-transition helper
  ([[0004-checkout-session]] §C) behind a `scopeExpirable` scope — the built
  select-then-`transitionTo` pattern can expire a session mid-payment and is on
  the rework list.
- All new public surface (future breaking changes need a Rector rule).
- Depends on `ValidateCartForOrderCreation`, `Cart::setShippingAddress()` /
  `setBillingAddress()` / `setShippingOption()` / coupon handling. The integrity
  fingerprint is **driver-owned** (§D): there is no dependency on
  `Cart::fingerprint()` / `checkFingerprint()`, the swappable
  `GeneratesFingerprint` binding, or `FingerprintMismatchException`.

## Acceptance checks

- The session table carries no FK to `lunar_carts`, `lunar_channels`, or
  `lunar_customers`; the order link is an opaque `order_reference` (no morph); a
  driver test creates a session from a non-Lunar source.
- A store verb (address/shipping/coupon) and a price-affecting element (donation)
  re-sync the session while `Open` — `amount_total` reflects the change and the
  session is **not** invalidated.
- An out-of-band line/quantity change while `Open` is reflected on the next render
  (the live snapshot + a fresh token, computed without writing the session row),
  not rejected.
- The pay-boundary gate **rejects** payment when the live cart fingerprint differs
  from the confirmed token (and re-renders the new total), and when
  `ValidateCartForOrderCreation` fails — even if the UI is bypassed. On success it
  pins `amount_total` **and the fingerprint** to the live values and the gateway is
  charged exactly that. An out-of-band change that bypasses the store verbs (e.g. a
  direct `setShippingAddress` from another tab) still changes the fingerprint — the
  token is a commitment to an exact amount.
- `complete()` re-verifies the live fingerprint against the pinned one inside the
  order-creation transaction; a duplicate callback or double-submitted synchronous
  completion yields exactly one order; a post-capture mismatch or order-creation
  failure refunds the charge and returns the session to `Open`
  (`CheckoutCompletionFailed`) — no charge survives without an order.
- Invalidating a `PaymentProcessing` session voids the intent first; an
  unabortable intent leaves the session in `PaymentProcessing` for §E.2 — never a
  terminal state with an unresolved charge.
- Creating a session supersedes a prior `Open` session for the same cart
  (`CheckoutSessionSuperseded`) and is refused with `409` while a sibling is in
  `PaymentProcessing`; two concurrent `createSession` calls yield one winner.
- A live-cart currency or channel change while `Open` invalidates the session
  rather than re-syncing it.
- A `GET`/render does not write the session row; staleness and the confirmation
  token are computed through the driver on demand.
- A `PaymentProcessing` session older than the reconciliation window is forced
  through the gateway-driver intent lookup and resolves (complete / refund /
  `Open`); exhausted attempts fire `CheckoutSessionReconciliationStalled`.
- A `PaymentProcessing` session that detects an out-of-band change is invalidated
  to `Cancelled` with a `meta` reason; a new session created from the cart carries
  the new lines.
- A price-neutral custom element stores into the bag via `putElementData()` and
  reads it back; the driver never sees it.
- Each §G event fires once for its trigger and carries the session.
- The fingerprint covers every payable input and only those: a change affecting
  **only shipping price or tax** changes the fingerprint; a **same-total
  shipping-address change** changes the fingerprint; a **guest→customer
  association does not** change the fingerprint.
- Gate step 0: a live currency/channel that no longer matches the pinned values
  is rejected at pay (`409`) with a void-first invalidation; a render only
  surfaces the divergence (DTO flag) and persists nothing.
- A store-verb re-sync racing the pay pin affects zero rows and is dropped —
  the pinned amount/fingerprint are never clobbered after the freeze (§D).
- `active_cart_reference` is unique while live and set `NULL` in the same UPDATE
  as every terminal transition; two concurrent first-creates for the same cart
  yield exactly one session via the unique index, with one supersede-retry on a
  lost create/create race (§F.2).
- Terminalizing an `Open` session that holds an advisory intent — supersede,
  expiry, explicit cancel — voids the intent first; an unknown-outcome void
  blocks the terminal transition (§F).
- A zero-total or offline (synchronous) completion runs the full §E gate inside
  `complete()`, in the order-creation transaction; `amount_total == 0` forces
  the synchronous path regardless of `requiresIntent()` (§E.2).
- The completion verify and the order build read from one consistent
  (`FOR UPDATE`) snapshot of the cart + lines/addresses; a completion-boundary
  refund carries an intent-derived idempotency key; `→ Open` happens only after
  the gateway confirms the refund/void, clears the intent ref +
  `payment_processing_at` and re-arms `expires_at`; a failed/unknown refund
  keeps the session in `PaymentProcessing` (§E.2).
- A gateway success event for a session not in `PaymentProcessing`, or for an
  intent that is not the session's current `payment_intent_ref`, is refunded —
  never completed (§E.2).
- A stalled session fires `CheckoutSessionReconciliationStalled` exactly once,
  drops to the low-frequency retry tier, refuses `createSession` with a
  `stalled` reason code, and exits only via the operator command or a
  confirmed-void supersede (§F).
- `ArchitectureTest`: actions implement a contract, expose `execute()`, import no
  facades. PHPStan L0 + Pint pass.

## Open questions

- ~~**Driver contract segregation.**~~ **Resolved:** one `CheckoutDriver`
  interface; third-party drivers MUST extend the shipped `AbstractCheckoutDriver`
  base class, where future verbs land with default implementations or explicit
  `…NotSupported` exceptions (§B).
- **`checkout-core` split.** The session/driver/integrity core in
  `packages/checkout` is transport-free by design; whether to split it from the
  transport/hosted surface into a separate `checkout-core` package later.
  (Owner: design.)
- ~~**Confirmation token transport.**~~ **Resolved:** the client submits the
  fingerprint it rendered ([[0003-transport-projections]] §A/§B). It is not a
  secret — the server recomputes and compares, and possession grants nothing the
  session `uuid` does not already grant — so a signed/server-side token adds no
  integrity. Revisit only if the token ever carries authority.
- **Price-affecting element contract.** The exact seam by which a donation/fee
  element adds its cart line through the driver (a dedicated verb vs the element
  calling a cart-line driver method). (Owner: design, with [[0001-core-element-model]].)
- **Read-verb caching for hosted render.** Deriving address/lines through the
  driver on each hosted page load is a backend round-trip; per-request cache vs a
  short-lived projection. (Owner: design, [[0005-hosted-checkout]].)
- **Resume after a long gap.** A session resumed days later is often re-synced to a
  changed total; confirm the render-time "total changed" hint is enough. (Owner: design.)

## References

- [[0004-checkout-session]] — the session, state machine, driver seam this spec
  refines (§A table, §B/§D superseded here).
- [[0002-payment-methods-and-driver]] — §E reconciliation backstop; the payment
  entry point that calls §E.
- [[0001-core-element-model]] — the element `store()` path and §C element bag.
- [[0007-discount-element]] — coupon writes via the store verb.
- [[0005-hosted-checkout]] — hosted render via the read verbs; confirmation token.
- `Lunar\Core\Models\Cart::setShippingAddress()` / `setBillingAddress()` /
  `setShippingOption()`,
  `Lunar\Core\Validation\Cart\ValidateCartForOrderCreation`,
  `Lunar\Core\Contracts\SupportsPaymentIntents` (new, §F). Core's
  `Cart::fingerprint()` / `checkFingerprint()` / `GeneratesFingerprint` dedupe
  machinery is explicitly **not** a dependency (§D).
