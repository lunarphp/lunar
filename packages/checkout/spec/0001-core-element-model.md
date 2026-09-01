# 0001 — Checkout Elements: core element model

- Status: draft
- Author: Alec Ritson
- Created: 2026-06-05
- TODO item: "Checkout Elements — core element model"

## Problem

There is no first-party abstraction for a checkout step. Consumers hand-wire contact, address,
shipping, and payment capture against the cart, repeating validation, completion tracking, and
serialization. Lunar needs a transport-agnostic model of a self-describing checkout element that
the REST API, the self-contained `lunar/checkout` Inertia app ([[0003-transport-projections]]),
and a future Lunar-hosted deployment ([[0005-hosted-checkout]]) all project from a single contract,
without any transport leaking into core.

## Proposal

A checkout element layer in `packages/checkout` (`Lunar\Checkout\…`) providing a capability-based
element contract, a manager that resolves an ordered element pipeline against a session-scoped
context, two server-side validation gates that write **through the cart API**, and a pure
`DataObjects/` DTO.

### A. Directory layout — the element model lives in `packages/checkout`

The element model layer — contracts, abstract element, registry, manager, DTOs, and (per
[[0004-checkout-session]] / [[0010-cart-session-reconciliation]]) the checkout session and
driver — lives in `packages/checkout` under the `Lunar\Checkout\…` namespace for now.
`lunar/core` gains **no** new top-level folder; the spec-0013 list is unchanged:

```
packages/checkout/src/                namespace Lunar\Checkout\…
├── AbstractCheckoutElement.php       required-core defaults
├── Elements/                         ContactInformation, AddressElement, ShippingOptionsElement, …
├── CheckoutManager.php               coordinator (implements Contracts\CheckoutManager)
├── ElementRegistry.php               registration + placement builder
├── ElementOrderResolver.php          deterministic ordering
├── CheckoutValidator.php             Gate-2 orchestration
├── Contracts/                        CheckoutManager, CheckoutElement, CapturesData, … (no `Interface` suffix)
├── DataObjects/                      CheckoutData, CheckoutElementData, CheckoutLayout
├── Enums/                            CheckoutRegion, AddressType
└── Events/                           CheckoutHydrated, CheckoutElementStored, OrderPlacing
```

A future transport-free `checkout-core` split — so a REST-only consumer does not pull in the
Inertia app — is recorded as an open question, not designed here.

### B. Capability interfaces

A small required core plus opt-in capabilities the manager checks with `instanceof`. This keeps
the contract additive — a new capability is not a breaking change for existing implementers —
and lets display-only elements exist without faking persistence. Contracts live in
`Lunar\Checkout\Contracts\` and drop the `Interface` suffix.

```
interface CheckoutElement {                         // required of every element
    setContext(CheckoutContext $context): static;   // §C — composed session context, not the raw Cart
    handle(): string;                                // unique; the manager enforces uniqueness
    title(): string;                                 // a translation key resolved via __()
    component(): string;                             // frontend registry key — a HINT (see 0003)
    mount(): void;                                   // READ-ONLY hydration (§E)
}

interface CapturesData {            // omit for display-only elements
    data(): array;                  // current persisted values → form seed (read-only)
    rules(): array;                 // STATIC rules — no branching on persisted state
    validationMessages(): array;
    validate(array $data): void;    // validates the INCOMING payload; base = Validator($data, rules)
    store(array $data): void;       // persists the VALIDATED subset THROUGH the cart API (§D)
}

interface ReportsCompletion {  isComplete(): bool;  required(): bool; }   // base isComplete derived from rules (§F)
interface ControlsVisibility { enabled(): bool;     visible(): bool; }     // read context
interface ProvidesProps {      props(): array; }                           // PURE, idempotent (§E)
interface DeclaresDependencies { dependsOn(): array; }                     // handles read; build-time validated
```

`AbstractCheckoutElement` implements the required core with defaults and lets a subclass adopt
capabilities by implementing the relevant interface. It does **not** implement `ProvidesProps`
(or any other capability interface) by default — capabilities are opt-in `instanceof` checks, so
a subclass that overrides/adds `props()` **without** implementing `ProvidesProps` is silently
ignored by the manager. A worked example contributing props must implement the interface
explicitly.

### C. Context — composed from session contracts

`setContext()` receives a `CheckoutContext` that **composes** the session context, not a raw
`Cart`. There are two resolution sources, transparent to the element:

- **Session-backed / hosted flow** (the normal path once [[0004-checkout-session]] lands):
  channel / currency are the session's **pinned** values; cart reads/writes resolve **through
  the active `CheckoutDriver`** against the session's opaque `cart_reference`
  ([[0010-cart-session-reconciliation]] §B) — there is no frozen-line copy; amount integrity is
  fingerprint-reconciled at the pay and completion boundaries
  ([[0010-cart-session-reconciliation]] §D–E.2). The pinned values override live
  `StorefrontSession`.
- **Session-less embedded flow:** channel / currency / customer-group / customer ←
  `StorefrontSession`; the cart ← `CartSession::current()`.

Checkout re-resolves none of these itself. The manager eager-loads the cart's
addresses/lines/shipping **once** in `hydrate()` so element accessors hit loaded relations (no
N+1; `preventLazyLoading`-safe). The forward seam is the planned Region concept /
`StorefrontContext`; `CheckoutContext` consumes it. An element is unit-testable with a fake
`CheckoutContext`.

The full `CheckoutContext` surface mirrors the driver verbs
([[0010-cart-session-reconciliation]] §B):

| | Verbs |
|---|---|
| **Write** | `storeContact($validated)` · `storeShippingAddress($validated)` · `storeBillingAddress($validated)` · `setShippingOption($identifier)` · `applyCoupon(?string $code)` — `null` maps to the driver's `removeCoupon` · `associateCustomer($customer)` · `putElementData($handle, $data)` |
| **Read** | `getShippingAddress()` · `getBillingAddress()` · `getShippingOptions()` · `getSelectedShippingOption()` · `getLines()` · `getCoupon()` · `getElementData($handle)` · `snapshot()` |

In the session flow every verb routes through the active `CheckoutDriver`; in the embedded flow
the verbs act on the live Lunar cart directly. Elements **never** receive or touch a session or
cart model — the context verb table is their entire surface. Elements that persist via
`putElementData` (the session element bag, [[0010-cart-session-reconciliation]] §C) **require the
session flow**: the embedded flow has no bag and no fallback — a bag-backed element is simply
unavailable session-less.

### D. Persistence goes through the cart API (not model writes)

`store($validated)` writes through the **context's typed write verbs** — never a model write,
and (since [[0010-cart-session-reconciliation]]) never a direct cart-method call from the
element. The context routes by flow: session-backed → the active `CheckoutDriver`'s store verbs
([[0010-cart-session-reconciliation]] §B), which re-sync the session; session-less embedded →
the Lunar cart methods directly. Either way the write lands on the cart's own API, which runs
the configured validators and `recalculate()`:

| Element | Write path |
|---------|-----------|
| `ContactInformation` | `$context->storeContact($validated)` → (driver) `storeContact` — guest email/phone ([[0010-cart-session-reconciliation]] §B); contact is **not** an address write |
| `AddressElement` | `$context->storeShippingAddress($validated)` / `storeBillingAddress($validated)` → (driver) → `Cart::setShippingAddress()` / `setBillingAddress()` (→ `AddAddress` action, `lunar.cart.validators.add_address`, tax-zone clear, recalc) |
| `ShippingOptionsElement` | `$context->setShippingOption($identifier)` → (driver) resolves via `ShippingManifest::getOption($cart, $identifier)` → `Cart::setShippingOption($option)` (→ `SetShippingOption` action + validator + recalc); persists identifier only |

Direct `fill()->save()` is forbidden: it bypasses the validators and the recalculation, letting
totals/tax drift from what payment integrity re-verifies. The element's transaction/lock wraps
the cart call; the action owns the persistence. `store()` consumes only the validated subset,
never raw input, never `forceFill`. Elements read persisted cart state for
`data()`/`isComplete()` through the context's read surface (mirroring the driver read verbs,
[[0010-cart-session-reconciliation]] §B), so an element stays correct when the backend cart is
not a Lunar model.

### E. Purity & lifecycle (normative)

- `mount()` is **read-only**. It may seed in-memory defaults from the customer
  (`LunarUser::latestCustomer()` + the authenticatable's email) **only when the cart has no
  value**. It MUST NOT write to the database (the prototype wrote on every GET render and
  overwrote user edits — forbidden).
- `props()` MUST be **pure and idempotent** — no side effects, no gateway calls, and it MUST NOT
  call `ShippingManifest::getOptions()` (which runs the `ShippingModifiers` pipeline, possibly
  hitting external rate APIs). Expensive/external data is a deferred prop in the transport layer
  ([[0003-transport-projections]]), not produced on the serialization path.
- `mount()` reads only persisted cart state, never another element's in-memory state.

### F. Completeness (defined, not author-invented)

`AbstractCheckoutElement::isComplete()` defaults to **all `required` fields in `rules()` present
and passing against current `data()`** — not "any field set". Authors override only for genuine
cross-field logic. The payment host element is `required(false)` for tick purposes (payment
success is established by the gateway, [[0002-payment-methods-and-driver]], not a pre-placement
boolean).

### G. Manager, registration, placement

`CheckoutManager` (implements `Lunar\Checkout\Contracts\CheckoutManager` — suffix dropped) is a thin
coordinator composing `ElementRegistry`, `ElementOrderResolver`, `CheckoutValidator`, and the
serializer. Collaborators are constructor-injected as promoted properties and bound to their
`Contracts/` interface in the checkout package's service provider (applying the spec-0016 rule);
no `app()`/facade resolution inside methods, and **no `config('lunar.checkout.*' =>
Class::class)` swap keys** — the container is the swap seam. The manager exposes
`getElement(string $handle): ?CheckoutElement` — the handle → element resolution seam the
transports use.

Elements register and are **placed** via a fluent builder (placement lives in registration, not
on the element):

```php
Checkout::add(ContactInformation::class)->region(CheckoutRegion::Main)->first();
Checkout::add(new AddressElement(AddressType::Shipping))->region(CheckoutRegion::Main);
Checkout::add(ShippingOptionsElement::class)->region(CheckoutRegion::Main)->after('shipping_address');
Checkout::add(new AddressElement(AddressType::Billing))->region(CheckoutRegion::Main);
Checkout::add(DiscountElement::class)->region(CheckoutRegion::Summary);
// the payment host element is contributed by 0002
```

`add()` returns a builder: `region(CheckoutRegion)`, `before/after(handle)`, `first()/last()`.
`ElementOrderResolver` topologically sorts within a region with a deterministic stable tiebreak
(registration index), **throws on cycles**, throws in dev / logs+appends in prod on a dangling
target, and validates every `dependsOn()` target exists.

**Octane:** registration is a build-time concern. The resolved ordered pipeline (class list +
placement) is built **once** and never rebound at runtime; the order-resolution dev-throw/prod-
append happens at build, before caching, so no per-request mutation of the cached structure
occurs. Per request only instantiation + `setContext()` + `mount()` run. Any container bindings
the registry needs are declared in `register()` (mirrors the spec-0021 Octane rule).

### H. Validation — two gates, on incoming data

- **Gate 1 (per element, on store):** validate the **incoming `$data`** via a transport-neutral
  `ValidationException` (a `StoreCheckoutElementRequest` FormRequest pulls `rules()` /
  `validationMessages()` from the resolved element; the transport renders failures — Inertia
  error bag or JSON:API errors, [[0003-transport-projections]]). On pass, `store($validated)`
  runs inside `DB::transaction()` with `lockForUpdate()` on the cart; persistence is the cart
  action.
- **Gate 2 (placement):** `CheckoutValidator` re-validates every `visible()` + `enabled()` +
  `required()` element and **delegates to the existing cart validators** — `Cart::canCreateOrder()`
  (the `order_create` validators) plus re-resolving the selected shipping option via
  `ShippingManifest::getOption($cart, $identifier)` — rather than hand-rolling invariants. Runs
  server-side; never trusts client gating. Gate 2 runs at the **pay boundary**
  ([[0003-transport-projections]] §A, [[0010-cart-session-reconciliation]] §E) **and again inside
  `complete()`** on the async path; on the synchronous path the pay boundary itself sits inside
  `complete()`, in the order-creation transaction, so it runs once there.

`rules()` are static; conditional requirements use `Rule::requiredIf()` on the incoming payload
or a `validate()` override — never branch on persisted state.

### I. Enums, events, i18n

- `Enums/CheckoutRegion` (`Express`/`Main`/`Summary`), `Enums/AddressType` (`Shipping`/`Billing`)
  — backed enums, TitleCase keys.
- `Events/`: `CheckoutHydrated`, `CheckoutElementStored`, `OrderPlacing` — **checkout-flow only**,
  with defined firing points (cross-referencing the session lifecycle events of
  [[0010-cart-session-reconciliation]] §G): `CheckoutHydrated` fires on each render/hydrate and
  persists nothing (renders never write); `CheckoutElementStored` fires after any successful
  element store, **alongside** the granular [[0010-cart-session-reconciliation]] §G event — one
  store fires both; `OrderPlacing` fires inside `complete()` immediately before order creation,
  on both the sync and async paths. Payment/order-lifecycle subscribers listen to core
  `OrderStatusUpdated` (and `PaymentAttemptEvent` for attempt telemetry); this spec does not
  re-emit those.
- All `title()` / message keys ship in the 16 Lunar locales (English first, 15 mirrored).

### J. DTO

`CheckoutData` (a `DataObjects/` value object) serializes per element: `handle, title,
component, required, data, props, rules, validationMessages, isComplete, enabled, visible,
dependsOn`, plus a `CheckoutLayout` (region → ordered handles). At the **top level** it also
carries the session-scoped fields the integrity chain needs on the client: `sessionUuid`,
`status`, `summary` (`subTotal`, `total`, `currencyCode` — display-formatted **and** minor
units), `confirmationToken` (the rendered fingerprint — the value the client returns at the pay
boundary, [[0010-cart-session-reconciliation]] §D–E), and `hasDrifted` (bool — the live cart has
diverged since the last render/pin; a render **surfaces** divergence, it never persists
anything). In the embedded session-less flow `sessionUuid` and `confirmationToken` are `null`.
It is **pure** — no envelope, no Inertia, no HTTP. The transports project it ([[0003-transport-projections]], [[0005-hosted-checkout]]);
generated TS types come off it. `component` and the layout are rendering hints. (If `DataObjects/` does not
already use `laravel-data`, this DTO uses the existing core DTO mechanism; introducing a new
dependency is out of scope here.)

## Alternatives considered

- **One fat element interface.** Rejected: ~16 mandatory methods make every addition a breaking
  change and force display elements to fake persistence. Capability interfaces are additive.
- **`region()` on the element.** Rejected: it makes the backend own frontend layout and
  contradicts the hybrid render; placement belongs in registration.
- **Element writes the model directly.** Rejected: bypasses cart validators + `recalculate()` →
  total/tax drift. The cart API is the write path.
- **Re-resolve channel/currency in checkout.** Rejected: `StorefrontSession` owns it; a parallel
  context diverges from the rest of the cart/catalogue.

## Migration impact

- The element model is greenfield in `packages/checkout` (`Lunar\Checkout\…`, §A); `lunar/core`
  gains no new top-level folder (spec-0013 list unchanged). New public contracts under
  `Lunar\Checkout\Contracts\`; no Rector yet, but future breaking changes need one.
- No schema change in this spec. Depends on `StorefrontSession`/`CartSession`, `ShippingManifest`,
  `Cart::set*Address`/`setShippingOption`, `LunarUser` — all existing.
- 16-locale keys: `lunar::checkout.elements.*` titles + validation messages.

## Acceptance checks

- `ArchitectureTest`: every element extends `AbstractCheckoutElement` and declares `handle()`;
  every `Actions/` class introduced implements a `Contracts/Actions/...` interface, exposes only
  `execute()`, and imports no service facades. PHPStan level 0 + Pint pass.
- Manager: registration ordering (`before/after/first/last`, forward refs, cycle throws, dangling
  target policy, load-order determinism); handle uniqueness; `dependsOn()` target validation;
  the §G `getElement(string $handle)` returns `?CheckoutElement` and a bad handle yields 404;
  `hydrate()` calls `mount()` in order with no lazy-load violation.
- Elements (fake `CheckoutContext` + factories): `rules()`; `validate($incoming)`; `store()`
  routes through `Cart::set*` and triggers recalc; `isComplete()` truth table;
  `enabled()`/`visible()` against context fixtures; `mount()` performs no writes.
- Gate 1 rejects invalid incoming data and rolls back the transaction; Gate 2 delegates to
  `Cart::canCreateOrder()` + shipping re-resolution and blocks on failure.

## Open questions

- **Resolved** (§C): the `CheckoutContext` surface is the §C verb table, mirroring the driver
  verbs ([[0010-cart-session-reconciliation]] §B); the test fake implements the same table.
- Whether `DataObjects/` adopts `laravel-data` or the existing core DTO mechanism. (Owner:
  package maintainer.)
- A future transport-free `checkout-core` split (element model + session + driver without the
  Inertia app), so a REST-only consumer does not depend on `lunar/checkout`'s frontend. (Owner:
  package maintainer.)
- **Resolved** ([[0007-discount-element]]): discount is a `Summary`-region `CapturesData` element,
  not a summary-component property; it persists via the context's `applyCoupon` verb — coupon
  mutation is driver-owned ([[0007-discount-element]] §B); `Cart::setCoupon()` is optional core
  sugar that can land independently.

## References

- [[0000-overview]], [[0002-payment-methods-and-driver]], [[0003-transport-projections]]
- [[0013-base-directory-reorganisation]], [[0016-service-layer-di]], [[0021-state-machines]]
- `Lunar\Core\Contracts\StorefrontSession`, `CartSession`, `LunarUser`;
  `Manifests\ShippingManifest`; `Models\Cart::setShippingAddress/setBillingAddress/setShippingOption`.
