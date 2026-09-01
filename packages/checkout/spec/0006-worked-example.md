# 0006 — Checkout Elements: worked example

- Status: draft (guide — illustrates the design in [[0000-overview]]; not a normative spec)
- Author: Alec Ritson
- Created: 2026-06-08

> The specs 0001–0005 fix the *architecture* in Problem/Proposal form. This document is the
> companion **walkthrough**: one reference checkout — contact → shipping address → shipping
> option → payment — followed end-to-end from cart to placed order through the self-contained
> `lunar/checkout` (Inertia) app. Code is **illustrative skeleton**, not compilable: load-bearing
> lines are shown, the rest is elided with `// …`, and any choice a spec leaves open is flagged
> inline so the example stays honest. The real Lunar v2 surfaces it leans on (`Cart::set*`,
> `ShippingManifest::getOption`, `Payments::driver`) exist on `2.x`; the `Checkout/` concern is the
> greenfield design those specs propose.

---

## 1. The mental model

A checkout is an **ordered list of elements**. Each element is a server-side PHP object that knows
how to validate and persist one slice of checkout data **through the cart API**, and declares which
frontend component renders it. The manager serializes the elements to a pure `CheckoutData` DTO;
every transport (Inertia, REST, hosted) projects that **same DTO**. Nothing about HTTP or Vue lives
in the element.

```
 register elements          server                    self-contained app (lunar/checkout)
 Checkout::add(...)   ─►  CheckoutManager  ──CheckoutData──►  <LunarCheckout/>
                              │  (pure DTO)                      │ registry: component() → Vue
                              │                                  │ posts back ▼
                         CapturesData::store()  ◄──/checkout/{uuid}/elements/{handle} {…data}
                              │
                         Cart::setShippingOption()  → validators + recalculate()
```

Three packages, one contract ([[0000-overview]]):

- **`lunar/checkout`** — everything checkout: the element contracts, the manager, the
  `CheckoutData` DTO, the `CheckoutSession` + driver, **and** the self-contained Inertia app
  (Lunar-owned, prebuilt, serves its own route — [[0008-checkout-ui-and-theming]]):
  `<LunarCheckout/>`, the Vue registry, `CheckoutProvider`, the `Checkout::add(...)` registration
  facade, the built-in element impls, the `/checkout/{uuid}/elements/{handle}` Inertia
  multiplexer. (A transport-free `checkout-core` split is an open question — [[0000-overview]].)
- **`lunar/core`** — the surfaces checkout rides: `Cart::set*`, `ShippingManifest`,
  `Payments`/`AbstractPayment` (plus the `SupportsPaymentIntents` capability). No checkout code.
- **`lunar/api`** — the REST projection of the same DTO (not the spine here; see [[0003-transport-projections]] §A).

This walkthrough uses the **Inertia spine**: the self-contained checkout app renders `<LunarCheckout/>`
on its own route and posts element data back through the multiplexer. REST appears only at the two
edges where the session is created and completed.

---

## 2. Define an element (PHP)

The shipping-options element. It captures the chosen shipping identifier, persists it **through the
cart** (never a direct model write — [[0001-core-element-model]] §D), and derives its own
completeness. It implements the required `CheckoutElement` core plus the `CapturesData`,
`ProvidesProps`, and `ReportsCompletion` capabilities ([[0001-core-element-model]] §B) —
capabilities are **instanceof-checked**, so overriding a capability method (here `props()`)
without declaring its interface is silently ignored.

```php
namespace Lunar\Checkout\Elements;

use Illuminate\Support\Facades\Validator;
use Lunar\Checkout\Contracts\CapturesData;
use Lunar\Checkout\Contracts\ProvidesProps;
use Lunar\Checkout\Contracts\ReportsCompletion;

class ShippingOptionsElement extends AbstractCheckoutElement implements CapturesData, ProvidesProps, ReportsCompletion
{
    public function handle(): string
    {
        return 'shipping_option';
    }

    public function title(): string
    {
        return 'lunar::checkout.elements.shipping_option.title'; // resolved via __()
    }

    public function component(): string
    {
        return 'shipping-options'; // frontend registry key — a HINT (§ 6), ignorable by a headless client
    }

    // READ-ONLY hydration. May seed in-memory defaults, MUST NOT write. (0001 §E)
    public function mount(): void
    {
        // nothing to seed — the selected option already lives on the cart
    }

    /** PURE + idempotent. Counts only because the class declares ProvidesProps (instanceof-
     *  checked). MUST NOT call the context's getShippingOptions() — that runs the
     *  ShippingModifiers pipeline (possibly external rate APIs). Options are a DEFERRED prop
     *  in the transport layer, not produced here. (0001 §E) */
    public function props(): array
    {
        return [
            // 'options' is injected as a deferred prop by lunar/checkout (§ 6), not here
        ];
    }

    public function data(): array
    {
        return [
            // context READ verb, driver-mediated (0010 §B) — NEVER $this->context->cart->…:
            // a non-Lunar driver has no Lunar cart, and mediation forbids reaching past the context
            'shipping_option' => $this->context->getSelectedShippingOption()?->identifier,
        ];
    }

    /** STATIC rules — no branching on persisted state. (0001 §H) */
    public function rules(): array
    {
        return [
            'shipping_option' => ['required', 'string'],
        ];
    }

    public function validationMessages(): array
    {
        return [
            'shipping_option.required' => __('lunar::checkout.elements.shipping_option.required'),
        ];
    }

    // Validates the INCOMING payload. Base impl = Validator($data, rules). (0001 §B/§H)
    public function validate(array $data): void
    {
        Validator::make($data, $this->rules(), $this->validationMessages())->validate();
    }

    /** Persists the VALIDATED subset through the context's typed write verb (0001 §D). The
     *  session-backed flow routes to the driver's setShippingOption (re-syncing the session,
     *  0010 §B); the Lunar driver resolves the identifier via ShippingManifest::getOption and
     *  hands off to Cart::setShippingOption — SetShippingOption action, validator, recalculate().
     *  A dangling identifier surfaces as a transport-neutral validation failure (0003 §H).
     *  Only the identifier persists. */
    public function store(array $data): void
    {
        $this->context->setShippingOption($data['shipping_option']);
    }

    /** Derived: all `required` rules present + passing against current data(). NOT "any field set".
     *  (0001 §F) — the default; override only for genuine cross-field logic. */
    public function isComplete(): bool
    {
        return parent::isComplete();
    }

    public function required(): bool
    {
        return true;
    }
}
```

Things the skeleton makes concrete:

- **`store()` routes through the context verb → driver → `Cart::setShippingOption()`** — the
  action runs validators and `recalculate()`, so tax/totals stay correct, and the driver re-syncs
  the session ([[0010-cart-session-reconciliation]] §B). A direct `fill()->save()` would skip
  both. The manager wraps the call in `DB::transaction()` + `lockForUpdate()` on the cart (Gate 1,
  [[0001-core-element-model]] §H); the element body itself just makes the context call.
- **`props()` does NOT enumerate shipping options.** That would fire the modifier pipeline on every
  serialization. The option list is a **deferred prop** the transport layer attaches (§ 6).
- **`$this->context`** is a composed `CheckoutContext` ([[0001-core-element-model]] §C), not a raw
  `Cart`. When a session exists, cart access is driver-mediated against the live source cart and
  its channel/currency are the session's *pinned* values ([[0004-checkout-session]] §D,
  [[0010-cart-session-reconciliation]]) — the element is identical either way.

`AbstractCheckoutElement` supplies `setContext()`, the default `mount()`/`props()`, and the derived
`isComplete()`; display-only elements simply omit `CapturesData`.

---

## 3. Register and place the elements

Placement lives in **registration**, not on the element ([[0001-core-element-model]] §G) — the
backend never owns frontend layout. A consumer wires their checkout in a service provider `boot()`:

```php
use Lunar\Checkout\Facades\Checkout;
use Lunar\Checkout\Elements\ContactInformation;
use Lunar\Checkout\Elements\AddressElement;
use Lunar\Checkout\Elements\ShippingOptionsElement;
use Lunar\Checkout\Enums\CheckoutRegion;
use Lunar\Checkout\Enums\AddressType;

public function boot(): void
{
    Checkout::add(ContactInformation::class)->region(CheckoutRegion::Main)->first();

    Checkout::add(new AddressElement(AddressType::Shipping))->region(CheckoutRegion::Main);

    Checkout::add(ShippingOptionsElement::class)
        ->region(CheckoutRegion::Main)
        ->after('shipping_address'); // forward/back refs by handle; resolver throws on cycles

    Checkout::add(new AddressElement(AddressType::Billing))->region(CheckoutRegion::Main);

    // The payment host element is contributed by lunar/checkout (§ 7); methods are registered
    // separately by whichever gateway package the store installs.
}
```

`add()` returns a placement builder — `region()`, `before()/after(handle)`, `first()/last()`.
`ElementOrderResolver` topologically sorts within each region, breaks ties by registration order,
and **throws on cycles or dangling targets in dev**. This resolution is a **build-time** concern:
the ordered pipeline is computed once and never rebound per request (Octane rule,
[[0001-core-element-model]] §G).

---

## 4. Begin checkout — create the session

Checkout begins when the cart is converted to a **`CheckoutSession`** ([[0004-checkout-session]]).
In the Inertia storefront this is the controller backing the "proceed to checkout" action; it calls
the same `POST /checkout` surface the REST projection exposes ([[0003-transport-projections]] §A).

```php
// storefront checkout controller (Inertia)
use Lunar\Checkout\Actions\CreateCheckoutSession;

public function start(CreateCheckoutSession $createSession): \Illuminate\Http\RedirectResponse
{
    $session = $createSession->execute(
        cart: $this->cartSession->current(),     // resolved via CartSession, never a request id
        storefront: $this->storefrontSession,    // channel/currency live here
    );

    return redirect()->route('checkout.show', $session->uuid);
}
```

`CreateCheckoutSession` (DI'd, `execute()`-only — spec 0016) does four things
([[0004-checkout-session]] §B):

1. **Pins** `channel_handle` + `currency_code` + `locale` from `StorefrontSession` — the pinned
   context the pay-time integrity anchors to ([[0010-cart-session-reconciliation]] §D).
2. Stores the **opaque `cart_reference`** to the live cart — no clone, no frozen copy
   ([[0010-cart-session-reconciliation]]): the session mirrors the cart while `Open` and the
   amount is pinned + confirmed at the pay boundary. A prior `Open` session for the cart is
   superseded; creation is refused while one is mid-payment
   ([[0010-cart-session-reconciliation]] §F.2).
3. Sets `expires_at` (and `success_url`/`cancel_url` if hosted, [[0005-hosted-checkout]]).
4. Returns the **`uuid`** — the public capability token. The internal `id` is never exposed.

From here the `uuid` is the addressable thing every read/write targets. It is unguessable, expiring,
rate-limited, and non-enumerable ([[0004-checkout-session]] §F) — which is exactly what lets a guest
resume, and lets the hosted page (0005) work without a login.

---

## 5. The DTO it emits

`GET /checkout/{uuid}` (REST) or the `checkout` Inertia prop both carry the **same**
`CheckoutData` — a pure `DataObjects/` value object, no envelope, no Inertia, no HTTP
([[0001-core-element-model]] §J). For our checkout it serializes roughly to:

```jsonc
{
  "sessionUuid": "f81d4fae-7dec-41d0-a765-00a0c91e6bf6",
  "status": "open",
  "summary": { "subTotal": 5499, "total": 6048, "currencyCode": "GBP" }, // LIVE-derived while Open
  "confirmationToken": "hmac:9f2c…",  // the driver-owned fingerprint the client echoes at POST …/pay (§ 8)
  "hasDrifted": false,                // live cart vs the last-synced snapshot
  "layout": {
    "main":    ["contact", "shipping_address", "shipping_option", "billing_address", "payment"],
    "summary": ["discount"],
    "express": []
  },
  "elements": {
    "shipping_option": {
      "handle": "shipping_option",
      "title": "Shipping method",
      "component": "shipping-options",   // rendering HINT — a headless client ignores it
      "required": true,
      "data": { "shipping_option": "royal-mail-tracked-48" },
      "props": {},                        // option list arrives as a deferred prop, see § 6
      "rules": { "shipping_option": ["required", "string"] },
      "validationMessages": { "shipping_option.required": "Choose a shipping method" },
      "isComplete": true,
      "enabled": true,
      "visible": true,
      "dependsOn": ["shipping_address"]
    }
    // … contact, shipping_address, billing_address, payment …
  }
}
```

The **contract** is the element data, completeness, validation, and dependencies — plus the
session-level fields ([[0001-core-element-model]] §J): `summary` is **live-derived** while `Open`
(nothing pins until pay), and `confirmationToken` is the driver-owned fingerprint the client
echoes back at `POST /checkout/{uuid}/pay` (§ 8) — all meaningful to any
client. `component` and `layout` are **rendering hints**: load-bearing for the Vue registry,
advisory for a native app or SSG ([[0003-transport-projections]] §D). Generated TypeScript types come
off this DTO, so the Vue layer below is fully typed.

> *Open question carried from [[0001-core-element-model]]:* whether `CheckoutData` is built with
> `laravel-data` or the existing core DTO mechanism. This example assumes `laravel-data` (so the
> TS-type generation in § 6 is automatic); the JSON shape is identical either way.

---

## 6. Render it (Vue, lunar/checkout)

The checkout app's own page mounts `<LunarCheckout/>` and passes the prop. The component reads the
layout and, for each handle, looks up the `component()` key in the registry and renders the matching
Vue component ([[0003-transport-projections]] §E).

```vue
<!-- resources/js/pages/Checkout/Show.vue -->
<script setup lang="ts">
import { LunarCheckout, registerCheckoutElement } from '@lunarphp/checkout'
import ShippingOptions from '@/checkout/ShippingOptions.vue'
import type { CheckoutData } from '@/types/generated' // generated off CheckoutData DTO

defineProps<{ checkout: CheckoutData }>()

// Override or add a component for a component() key. Built-in keys are pre-registered.
registerCheckoutElement('shipping-options', ShippingOptions)
</script>

<template>
  <!-- single root element (Inertia + Vue rule) -->
  <LunarCheckout :checkout="checkout" />
</template>
```

A single element component. It consumes its slice of the DTO, reads the **deferred** option list, and
posts the selection back through the multiplexer. State comes from the `CheckoutProvider` inject —
**never** a module-level singleton (those bleed across concurrent SSR requests — a PII leak,
[[0003-transport-projections]] §F).

```vue
<!-- resources/js/checkout/ShippingOptions.vue -->
<script setup lang="ts">
import { useCheckout } from '@lunarphp/checkout' // provide/inject scoped to <LunarCheckout>
import type { CheckoutElementData } from '@/types/generated'

const props = defineProps<{ element: CheckoutElementData | undefined }>() // optional: may unmount

const { store, deferred, errors } = useCheckout()
// options are a deferred prop (firing the modifier pipeline) — render a skeleton until they land
const options = deferred('shipping_option.options')
// `errors` surfaces the Inertia ERROR BAG, keyed by element handle (0003 §H) —
// validation failures are NOT a field on the element DTO

function select(identifier: string) {
  // POST /checkout/{uuid}/elements/{handle} { ...data } — Gate 1 + coarse cart write +
  // recomputed DTO in the SAME response (no second router.reload). Debounced; merges
  // without clobbering.
  store('shipping_option', { shipping_option: identifier })
}
</script>

<template>
  <fieldset v-if="element">
    <legend>{{ element.title }}</legend>

    <template v-if="options.loaded">
      <label v-for="opt in options.value" :key="opt.identifier">
        <input
          type="radio"
          name="shipping_option"
          :value="opt.identifier"
          :checked="element.data.shipping_option === opt.identifier"
          @change="select(opt.identifier)"
        />
        {{ opt.name }} — {{ opt.price.formatted }}
      </label>
    </template>

    <!-- deferred prop: animated skeleton while the option list resolves (0003 §F) -->
    <ShippingSkeleton v-else />

    <!-- errors come from the Inertia error bag scoped to this element's handle (0003 §H) -->
    <p v-if="errors.shipping_option?.shipping_option" role="alert">
      {{ errors.shipping_option.shipping_option }}
    </p>
  </fieldset>
</template>
```

What the skeleton encodes:

- **`element` is `CheckoutElementData | undefined`** and the body is `v-if`-guarded — when an element
  flips `visible: false` or drops out, it unmounts cleanly instead of crashing.
- **`store()` posts to the `/checkout/{uuid}/elements/{handle}` multiplexer**, which runs Gate 1,
  dispatches to the same coarse cart write the REST projection exposes
  (`/checkout/{uuid}/shipping-option`), and returns the recomputed `CheckoutData` **in the same
  response** — scoped to the stored element plus any whose `enabled()`/`visible()` flipped.
  Deferred props are not re-sent. Validation failures arrive via the per-element Inertia error
  bag, not the DTO ([[0003-transport-projections]] §H).
- The option list is **deferred** — the expensive `ShippingManifest::getOptions()` call lives in the
  transport layer, never in `props()`.
- An **unknown component key** would render a visible dev fallback + console error; a CI check in
  `lunar/checkout` diffs backend keys against registered Vue keys ([[0003-transport-projections]] §E).

---

## 7. A payment method (optional gateway)

Payment rides Lunar's **existing** gateway-agnostic seam — `Payments` + `PaymentTypes\AbstractPayment`
— not a parallel abstraction ([[0002-payment-methods-and-driver]] §A). A `PaymentMethod` is a thin
presentation+capability descriptor that names a registered driver. Core ships **no** method; a store
registers whatever gateway(s) it installs. Stripe is one *optional* reference:

```php
// in lunar/stripe (optional) or the host app — NEVER in lunar/core
namespace Lunar\Stripe\Checkout;

use Lunar\Checkout\Contracts\PaymentMethod;

class StripeCardMethod implements PaymentMethod
{
    public function handle(): string        { return 'stripe_card'; }
    public function label(): string         { return 'lunar::stripe.methods.card'; } // translation key
    public function icon(): string          { return 'credit-card'; }
    public function driver(): string        { return 'stripe'; }   // key of an AbstractPayment driver
    public function requiresIntent(): bool  { return true; }       // async path: intent + SDK confirm (0002 §A)
    public function component(): string     { return 'stripe-card'; } // inline Vue component (a hint)
    public function supportsExpress(): bool { return false; }
    public function expressComponent(): ?string { return null; }
}
```

```php
// gateway/host provider boot() — type-checked + allowlisted at boot (0002 §B)
use Lunar\Checkout\Facades\Payment;
use Lunar\Checkout\Support\CheckoutAssets;

// (a) the server descriptor — which driver, label, component() KEY
Payment::registerMethod(StripeCardMethod::class);

// (b) the FRONTEND half — register a prebuilt chunk that self-registers the Vue
//     component for the 'stripe-card' key. One call; served same-origin via the
//     asset route — NO vendor:publish, NO consumer rebuild, NO app publish (0009 §C)
CheckoutAssets::register(
    package: 'lunar-stripe',
    source:  __DIR__.'/../resources/dist',   // the gateway package's OWN chunk dir
    entry:   'checkout.js',
    compat:  '^1.0',
);
```

The **payment host element** (placed `region(Main)->last()`, contributed by `lunar/checkout`) renders
the registered methods as tabs; methods with `supportsExpress()` also project into the `express`
region as wallet buttons — same objects, different placement ([[0002-payment-methods-and-driver]] §C).
With no method registered the payment region renders empty and Gate 2 reports the order not placeable —
core stays gateway-free.

The method's `component()` ("stripe-card") reaches the client not by baking it into Lunar's prebuilt
app but via the chunk registered in (b): a prebuilt ES module the gateway ships, loaded at runtime, that
self-registers the component into the app's registry over a **shared** Vue + SDK (`window.Vue` /
`window.Lunar` externals) — so the gateway's UI appears with **no fork, no consumer build, and no app
publish** ([[0009-frontend-element-extension]]).
First-party gateways (Stripe/PayPal/Opayo) are prebuilt straight into the shipped app, so the common
store needs no chunk at all.

On the client, the active method exposes a **typed submit seam** via `defineExpose`, registering into
the `CheckoutProvider` on mount ([[0003-transport-projections]] §F):

```vue
<!-- StripeCard.vue — compiled into lunar/stripe's prebuilt checkout chunk (0009).
     `vue` and `@lunarphp/checkout` are externalised to window.Vue / window.Lunar → the app's
     SINGLE shared instances, so useCheckout()'s inject resolves the CheckoutProvider across the boundary. -->
<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useCheckout } from '@lunarphp/checkout'

const ready = ref(false)
const { registerPaymentMethod, paymentIntent } = useCheckout()

async function submit(): Promise<void> {
  // the generic Pay action has ALREADY posted /checkout/{uuid}/pay — gate, pin,
  // PaymentProcessing, intent re-pinned to the pinned amount — before submit() runs (§ 8, 0010 §E).
  // Gateways needing a pre-confirmation intent create it via POST /checkout/{uuid}/payment-intent,
  // which delegates to Payments::driver('stripe') and records payment_intent_ref (0002 §A, 0003 §A)
  const { clientSecret } = await paymentIntent('stripe_card')
  // … confirm with the gateway SDK using clientSecret …
}

onMounted(() => registerPaymentMethod({ handle: 'stripe_card', submit, ready }))
// the generic Pay action stays disabled (with an aria-describedby reason) until `ready` (0003 §F/§G)
</script>
```

Core only declares which method is active-eligible; the driver owns intent/confirm/capture and all
payment-integrity logic — **reference it, don't reinvent it** ([[0002-payment-methods-and-driver]] §E).

---

## 8. Complete — session → order

The pre-confirm server round-trip is **`POST /checkout/{uuid}/pay`**
([[0003-transport-projections]] §A): it runs the pay-boundary gate, pins, and freezes the session
**before** the client ever confirms with the gateway SDK. Completion is
`POST /checkout/{uuid}/complete` (or the gateway webhook), which re-runs the gate against the
session's **pinned** values **inside the order-creation transaction** — on the synchronous path
(`requiresIntent() === false`, and always for zero-total carts) `/complete` carries the token and
is the only round-trip. Neither is a direct model write:

```
client clicks pay → POST /checkout/{uuid}/pay, submitting the CONFIRMATION TOKEN
   │                (the driver-owned fingerprint it rendered)
   ▼
pay-boundary gate (0010 §E): step 0 — live currency/channel == pinned values;
   │   live fingerprint == confirmed token? cart orderable?
   │   mismatch → 409, re-synced totals + fresh token, customer re-confirms
   ▼
session: Open → PaymentProcessing        ← guarded single-statement pin of amount_total +
   │                                       fingerprint; element stores rejected
   │   intent re-pinned to the pinned amount, payment_intent_ref recorded — all BEFORE
   │   the /pay response returns (0010 §E)
   ▼
client confirms with the gateway SDK; gateway responds (webhook and/or redirect return)
   │   driver verifies intent.amount/currency == session pinned total (0002 §E)
   │   success event for a NON-CURRENT intent or a non-PaymentProcessing
   │   session → REFUND, never complete (0010 §E.2)
   ▼
completion boundary (POST /checkout/{uuid}/complete or the webhook): live fingerprint ==
        PINNED fingerprint, inside the order-creation transaction (0010 §E.2); Gate 2
        re-runs every visible+enabled+required element, delegates to Cart::canCreateOrder() (0001 §H)
   │   mismatch / failure after capture → REFUND, session → Open (no charge without an order)
   ▼
session: PaymentProcessing → Completed    (terminal; order_reference written)
order:   created at AwaitingPayment → InProcess, placed_at stamped,
         Transaction rows recorded by the driver (0002 §D)
```

Three guarantees this collapses into one place:

- **Amount integrity** — the customer's confirmation token must match the live cart at the gate,
  the pinned amount feeds the gateway, and the completion boundary re-verifies the pinned
  fingerprint before the order exists; a post-capture mismatch refunds — no charge without an
  order ([[0010-cart-session-reconciliation]] §D–E.2).
- **The freeze** — `PaymentProcessing` rejects element stores (returns `409`/`422` through each
  adapter's error projection, [[0003-transport-projections]] §H); there is no cart `status` column.
- **Idempotency** — the `→ Completed` transition is an atomic guarded write, so a
  webhook+redirect race, a double callback, or a double-submitted synchronous completion produces
  **exactly one** order ([[0004-checkout-session]] §E). A stale `PaymentProcessing` session is
  force-reconciled on a bounded schedule — never blindly expired, never left forever
  ([[0010-cart-session-reconciliation]] §F).

Note there is **no `paid` order state and no `PaymentStatus` enum** — payment success *is* the
`AwaitingPayment → InProcess` transition plus the `transactions` rollup ([[0002-payment-methods-and-driver]] §D,
[[0021-state-machines]]). Bespoke states only via the `OrderStateConfig` seam.

---

## Where the same checkout goes headless or hosted

The walkthrough used the Inertia spine, but every step above is a projection of `CheckoutData`:

- **REST (`lunar/api`)** — the same flow without Inertia: `POST /checkout` → the coarse
  `/checkout/{uuid}/shipping-option` (etc.) writes → `POST /checkout/{uuid}/payment-intent` →
  `POST /checkout/{uuid}/pay` → `POST /checkout/{uuid}/complete`, JSON:API envelope and
  `errors[]` ([[0003-transport-projections]] §A/§H).
- **Hosted (future)** — the **same self-contained app** under a Lunar-operated deployment resolves
  the `uuid`, serves the **same** `<LunarCheckout/>`, drives the **same** coarse writes, and redirects
  to `success_url` on completion. A default element set + payment-method-from-config replace the
  merchant's service provider ([[0005-hosted-checkout]]).

One element model, one DTO, one `<LunarCheckout/>` — three transports.

## References

- [[0000-overview]], [[0001-core-element-model]], [[0002-payment-methods-and-driver]],
  [[0003-transport-projections]], [[0004-checkout-session]], [[0005-hosted-checkout]],
  [[0009-frontend-element-extension]]
- Real Lunar v2 surfaces used: `Lunar\Core\Models\Cart::setShippingAddress/setBillingAddress/setShippingOption/canCreateOrder`;
  `Lunar\Core\Facades\ShippingManifest::getOption($cart, $identifier)`; `Lunar\Core\Facades\Payments::driver($key)`;
  `Lunar\Core\PaymentTypes\AbstractPayment`.
