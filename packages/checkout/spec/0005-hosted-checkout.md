# 0005 — Checkout Elements: hosted checkout (future)

- Status: draft (future — awareness now, build on the session + package later)
- Author: Alec Ritson
- Created: 2026-06-05
- TODO item: "Checkout Elements — Lunar-hosted checkout page"

## Problem

Some merchants run no Laravel app at all, or want to offload the checkout surface (PCI, payment
UI, hosting, maintenance) entirely — a headless catalogue, a marketplace seller, or a "pay by
link" flow. `lunar/checkout` is already a **self-contained Inertia app** that serves its own route
([[0008-checkout-ui-and-theming]]), so a merchant *with* a Laravel app already gets a working
checkout they link to. What's missing is the **same app operated by Lunar** for the merchant who
has no app to host it in — rendered from a session UUID alone, the analogue of Stripe's hosted
Checkout page a merchant redirects a customer to.

The `CheckoutSession` ([[0004-checkout-session]]) already makes a checkout self-contained,
addressable, and resumable by UUID. Hosted checkout is therefore **not a new architecture and not
a new render stack** — it is the **same self-contained `lunar/checkout` app under a Lunar-operated
deployment**, resolving a session by UUID. This spec is forward-looking: it records the shape so
the session and the app stay deployment-compatible with it, but it is the last to be built.

## Proposal

A Lunar-operated deployment of the self-contained `lunar/checkout` app (working name
`lunar/hosted-checkout`, or a Lunar Cloud feature) that:

1. resolves a `CheckoutSession` by its UUID,
2. serves the **same self-contained app** ([[0008-checkout-ui-and-theming]] §H) — the same
   `<LunarCheckout>` and prebuilt assets — deriving the session's `CheckoutData` **live** through
   the checkout driver server-side (while `Open` nothing is frozen; only amount + fingerprint
   pin, and only at pay — [[0010-cart-session-reconciliation]] §D/§E),
3. drives element stores + payment confirmation through the same core paths, and
4. on completion redirects to the session's `success_url` (or `cancel_url`).

It is a **deployment mode** of the same app, not a new projection or render stack. It owns no
checkout logic of its own.

### A. Entry & exit (redirect flow)

- The merchant's backend creates a session — `POST /checkout` ([[0003-transport-projections]] §A) —
  passing `success_url`/`cancel_url`, and receives the `uuid` + a hosted URL.
- The merchant redirects the customer to `https://<hosted-host>/checkout/{uuid}`.
- On confirmed payment the page completes the session ([[0004-checkout-session]] §E) and redirects
  to `success_url`; an explicit cancel redirects to `cancel_url`. Both are allowlist-validated
  ([[0004-checkout-session]] §F) — open-redirect prevention.
- Expired/terminal sessions render a terminal view, never the form.

### B. Surface

- `GET /checkout/{uuid}` — resolve the session, render the **live-derived** summary + element
  DTO/layout through the driver's read verbs (reads never write), standalone (no merchant
  frontend). Guest access via the UUID capability ([[0004-checkout-session]] §F);
  no merchant login.
- Element stores map to the same coarse cart writes (`/shipping-address`, `/shipping-option`,
  `/coupons`, …) the REST projection exposes, routed through the checkout driver's store verbs
  ([[0010-cart-session-reconciliation]] §B); the Pay action posts the confirmation token to
  `POST /checkout/{uuid}/pay` like any other client ([[0010-cart-session-reconciliation]] §E).
- Payment confirmation and `payment-intent` use the registered gateway driver
  ([[0002-payment-methods-and-driver]]).

### C. Who registers the elements & methods

In the self-hosted case the developer registers elements in their service provider. The hosted page
has **no merchant service provider**, so the hosted app owns a **sensible default element set**
(contact + shipping/billing address + shipping options + payment host) and selects **payment
methods from merchant/channel config** (whichever gateway the merchant connected). The merchant
configures *which* methods and *branding*, not *which elements* — element composition is the
hosted default, optionally narrowed by config. This keeps the hosted page gateway-agnostic
(Stripe optional) and frontend-free for the merchant.

### D. Rendering & reuse

The hosted deployment serves the same `lunar/checkout` app and its `<LunarCheckout>` (one registry
of `component()`-keyed components, [[0003-transport-projections]] §E) so a method/element renders
identically whether the merchant self-hosts the app or Lunar operates it — it does **not** fork the
components or maintain a second build. Branding is token-driven per merchant/channel (logo/colours
via the `CheckoutTheme` seam, never hardcoded, [[0008-checkout-ui-and-theming]] §D). Hosting is the
only thing Lunar adds over the self-hosted app.

### E. Security

UUID capability bearer model ([[0004-checkout-session]] §F): unguessable, expiring,
non-enumerable, rate-limited. Plus, because Lunar serves the page: `no-store`; a CSP appropriate
to a payment page; return URLs allowlisted; PII (addresses/email) rendered only within the
session's own page and never logged. The `PaymentProcessing` state guards against concurrent edits
mid-confirmation.

## Alternatives considered

- **`storefront-ui` as the hosted page** (mount the reference storefront on a Lunar route).
  Rejected: it couples Lunar's hosted offering to a demo's build; the hosted shell consumes the
  same `lunar/checkout` library directly instead.
- **A second, hosted-only element/render stack.** Rejected: the whole point of the session + DTO +
  `<LunarCheckout>` is that hosted is a thin reuse, not a parallel implementation.
- **Blade-only hosted page** (zero JS deps) or **a second hosted-only render stack**. Rejected: the
  rendering stack is settled — hosted serves the same self-contained Inertia app
  ([[0008-checkout-ui-and-theming]] §H), so there is no second renderer to choose.
- **Build hosted now.** Deferred: it depends on the session (0004) and the self-contained
  `lunar/checkout` app ([[0008-checkout-ui-and-theming]]) landing first; specifying it now only
  constrains those to stay deployment-compatible.

## Migration impact

- New Lunar-operated deployment of the self-contained `lunar/checkout` app, consuming `lunar/core`
  (session + DTO). It runs the same prebuilt app ([[0008-checkout-ui-and-theming]] §A/§H), not a new
  render stack. **No** new core schema beyond the session ([[0004-checkout-session]]);
  `success_url`/`cancel_url` already live on the session.
- A default element-set + method-from-config registration path for the merchant-frontend-less case.
- 16-locale strings for the hosted shell (terminal/expired/empty/error/branding views).
- Net-additive public surface (the hosted route contract + branding tokens).

## Acceptance checks

- `GET /checkout/{uuid}` renders a payable checkout from the session alone, no merchant frontend,
  no core dependency on a specific gateway.
- Completing the hosted checkout creates the order (idempotently, [[0004-checkout-session]] §E) and
  redirects to `success_url`; cancel redirects to `cancel_url`; both are allowlist-validated.
- Expired/terminal sessions render a terminal view, never the form.
- A method registered for the store via config appears and completes payment on the hosted page
  identically to the self-hosted `<LunarCheckout>`.
- Security: unguessable UUID, expiry enforced, return URLs allowlisted, `no-store`/CSP set.

## Open questions

- Deployment model: Lunar Cloud SaaS (multi-tenant) vs a self-hostable package the merchant
  deploys. (Owner: product/design.)
- Domain: a Lunar-owned host vs the merchant's domain via CNAME/custom domain. (Owner: product.)
- How a merchant-frontend-less store customises the element set/branding without a service provider
  (config schema vs a lightweight dashboard). (Owner: design.)
- **Resolved:** the rendering stack is the self-contained `lunar/checkout` Inertia app
  ([[0008-checkout-ui-and-theming]]); hosted serves the same prebuilt app, no second renderer and no
  Lunar-internal fork.

## References

- [[0000-overview]], [[0001-core-element-model]], [[0002-payment-methods-and-driver]],
  [[0003-transport-projections]], [[0004-checkout-session]], [[0008-checkout-ui-and-theming]]
- Stripe hosted Checkout — UX/redirect reference (not carried forward).
