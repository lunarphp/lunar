# Resume — checkout session work

Pick up the Lunar checkout-session work. Context:

## Status

- **Specs: round-2 hardened (2026-06-12).** The 4-reviewer senior-panel REQUEST-CHANGES has been
  applied across the set (`0000`–`0010`) — see `overview.md`'s "Round-2 hardening" changelog bullet
  for the full delta. Headlines: driver-owned HMAC fingerprint (not core's `Cart::fingerprint()`);
  `POST /checkout/{uuid}/pay` (+ `/contact`, `/elements/{handle}`) transport verbs; advisory-intent
  ownership + the refund-never-complete webhook rule; void-first on every terminalizing transition;
  `active_cart_reference` unique-column concurrency; single-statement guarded session writes
  (guarded-transition helper = concurrency layer, spatie machine = legality layer); gate step 0
  (currency/channel); sync-path gate inside `complete()`'s order transaction; the
  `SupportsPaymentIntents` core capability seam; stall protocol + operator resolve; renames
  (`hasDrifted()`, `scopeExpirable`, `cancelled_at`, `config('lunar.checkout.driver')`); zero-total
  carts force the sync path; package home pinned to `packages/checkout` (`Lunar\Checkout\…`).
- **Implementation: session-core slice LANDED (2026-06-12).** Everything in the Rework/Add lists
  below is built except: the retention prune (deferred), the prototype element bag
  (`Contracts/CheckoutSession` + `Session/CheckoutSession` — left in place until the 0001 element
  layer is reworked to `CheckoutContext`; `AbstractCheckoutElement` still depends on it), and the
  0003 transports (`/pay`, `/contact`, `/elements/{handle}` — next phase). Verified: Pint clean,
  PHPStan level 0 clean, `--testsuite=checkout` 19 passed / 1 skipped (the skipped render test
  needs the published Vite build). 16-locale lang mirror done (English placeholders).

## Built code DIVERGES from the design — adjust in place (do NOT discard)

### Rework
- Migration — `order_reference` string (no morph), plus the 0010 §H additions:
  `active_cart_reference` (nullable + plain unique index — partial indexes don't exist on MySQL),
  `payment_processing_at`, `reconciliation_attempts`, `pruned_at`.
- `CheckoutSession` model, `CreateCheckoutSession` action (supersede + `409` concurrency rules),
  `CheckoutDriver` contract + `LunarCheckoutDriver` (new verbs: `storeContact`,
  `getSelectedShippingOption`, `getCoupon`; apply/remove coupon split).
- `database/factories/CheckoutSessionFactory.php` — still fakes `cart_id`/`channel_id`/`customer_id`;
  move to the neutral references, add the new columns, add a `paymentProcessing` factory state.
- `tests/checkout/Feature/CreateCheckoutSessionTest.php` — asserts the dropped Lunar-FK columns.
- The prototype element bag — `Contracts/CheckoutSession.php` + `Session/CheckoutSession.php` +
  the scoped binding — is superseded by the persisted `element_data` bag; delete or rename.
- `ExpireCheckoutSessions` — **off the survives list**: its select-then-`transitionTo` can stomp a
  session mid-payment; it must use the guarded-transition helper + void-first on advisory intents.
- Console also gains the bounded reconciliation sweep + the operator resolve command
  `lunar:checkout:reconcile {uuid} --resolve=complete|refund|cancel`.

### Add
- `CartSnapshot` / `CheckoutAddress` DTOs (`CartSnapshot` now carries `currencyCode` /
  `channelHandle` / `hasAppliedDiscount` / `couponCode`; `CheckoutAddress` fields pinned),
  `SyncCheckoutSession` / `InvalidateCheckoutSession` / `ReconcileCheckoutSession` actions +
  contracts, the 0010 §G events, the element-bag API, the retention prune.
- Checkout `ActionServiceProvider` — the built `CreatesCheckoutSession` contract is **never bound**,
  so the driver can't resolve it.
- `tests/checkout/Unit/ArchitectureTest.php` — mirror core's arch rules (actions implement a
  contract, expose `execute()`, no facade imports).
- Core `SupportsPaymentIntents` interface (`packages/core` — small, additive:
  fetchIntent/voidIntent/refundIntent).
- `AbstractCheckoutDriver` base class (mandatory for third-party drivers).
- The guarded-transition helper (single-statement guarded UPDATEs over the spatie machine).
- The new columns/indexes per 0010 §H (with the migration rework above).

### Survives unchanged
- `States/CheckoutSession/*` + states config + contract.
- `CheckoutSessionManager` (config key is `lunar.checkout.driver`).
- Provider/config/composer scaffolding — plus the new `ActionServiceProvider`; note
  `composer.json` `"type"` should be `"library"`, not `"project"`.

## Deferred (flagged, not blocking)
- The built asset pipeline (`vendor:publish` + Vite) contradicts 0008 §A's no-publish asset-route
  model — flagged; does not block the session work.

## Notes
- Environment: vendor was out of sync — `composer update` was required and is **now done**;
  Pest/PHPStan run.
- 16 locales: only `en` exists for the state-label lang file; mirror the keys across the other 15
  in the implementation PR.
