# 0009 — Checkout element & gateway frontend extension

- Status: draft
- Author: Alec Ritson
- Created: 2026-06-09
- TODO item: "Checkout Elements — third-party element/gateway frontend contribution into the self-contained app"

## Problem

[[0008-checkout-ui-and-theming]] makes `lunar/checkout` a **self-contained Inertia app that ships
prebuilt** (`dist/`) so it installs and runs with no consumer build. That sealed bundle has a glaring
gap the moment a third party needs to contribute UI: **a prebuilt bundle Lunar ships cannot contain a
Vue component Lunar does not know about.**

This breaks the core Lunar model. Lunar's payment story is gateway *packages* — `lunar/stripe`,
`lunar/paypal`, `lunar/opayo`, plus arbitrary third-party gateways — and the element model
([[0001-core-element-model]]) is explicitly built so developers register **their own** checkout
elements. The backend half already works: `Payment::registerMethod(...)` and `Checkout::add(...)` let
any package contribute a `PaymentMethod` or a `CheckoutElement` server-side. But every such element
declares a `component()` key that must resolve to a **Vue component on the client** — and under the
sealed-bundle model the only way to get that component into the app is to publish the whole source and
rebuild (the publish-and-own cliff, [[0008-checkout-ui-and-theming]] §E). That means:

- installing a payment gateway does **not** make its UI appear in the checkout, and
- "add your own element for others to use" requires every consumer of that element to fork and rebuild
  the checkout app.

Since nearly every real store installs at least one payment gateway, an extension path that forces a
rebuild would make install-and-go apply to almost nobody. The contract surface for *backend*
registration exists; this spec defines its missing **frontend mirror**: how a package contributes a
checkout component into the prebuilt app **at runtime, with no consumer rebuild and no fork**.

## Proposal

Mirror the backend registration seam on the frontend. A package that contributes a checkout element or
payment method ships a **small prebuilt ES-module chunk** that **self-registers** its Vue component
into the app's existing component registry ([[0003-transport-projections]] §E) at runtime. The app
loads each registered chunk; the chunk calls `registerCheckoutElement(key, component)`. The sealed
bundle stays sealed — extensions arrive **alongside** it, not inside it.

```
lunar/stripe  (or any package — first-party, third-party, or the host app itself)
  ├─ PHP (server, mostly exists today)
  │    Payment::registerMethod(StripeCardMethod::class)        // 0002 §B — server descriptor
  │    CheckoutAssets::register('lunar-stripe', asset_url, ?compat)   // NEW — publish the JS chunk URL
  │
  └─ JS (prebuilt by the PACKAGE, Vue + SDK externalised)
       resources/dist/checkout.js   (an ES module, served same-origin via the asset route, §C.1)
         import { registerCheckoutElement } from '@lunarphp/checkout'  // → window.Lunar (§B)
         import StripeCard from './StripeCard.vue'   // compiled into this chunk
         registerCheckoutElement('stripe-card', StripeCard)
```

Loading flow, all at runtime:

1. The checkout app's Inertia root view collects every URL registered via `CheckoutAssets` (from all
   installed packages) and emits one `<script type="module" src="…">` per chunk, after the app boots.
2. Each chunk imports `vue` and `@lunarphp/checkout` as **externals** resolving to the app's shared
   `window.Vue` / `window.Lunar` globals (§B) — the app's single instances — and self-registers its
   component(s) into the registry by `component()` key.
3. `<LunarCheckout>` renders elements from `CheckoutLayout`; when it reaches `component: "stripe-card"`
   the registry now has it. A key not yet registered renders the existing dev fallback
   ([[0003-transport-projections]] §E), never a crash.

This is **tier 2** of [[0008-checkout-ui-and-theming]] §E — "add an element/gateway without a fork or a
consumer rebuild" — and is the seam that makes the self-contained app genuinely extensible.

**Contribution is orthogonal to publish-and-own.** Adding a checkout element (or gateway) and its
JavaScript requires **neither** publishing the checkout app's source/assets
(`lunar.checkout.source`, tier 3 of [[0008-checkout-ui-and-theming]]) **nor** rebuilding the app.
These are two independent axes, and conflating them is the trap this spec exists to avoid:

- **Publish-and-own** (tier 3) is for a merchant who wants to rewrite the checkout's *own* markup and
  behaviour — they take ownership of the whole app and its build.
- **Contribute an element** (this spec, tier 2) drops a self-registering component into the
  **unmodified, prebuilt** app. The contributor ships only its *own* small chunk; the app's `dist/`
  is never published, owned, or rebuilt.

A developer adds their own element + JS with **zero** interaction with the checkout app's assets.
This holds for a distributable package (`lunar/stripe`) **and equally for a one-off bespoke element a
merchant writes in their own app** (§C). Statamic proves the pattern: an addon registers a script
that loads into the prebuilt Control Panel; the merchant never publishes or rebuilds the CP to use it.

### A. The registry is the contract; registration is symmetric

The frontend registry `registerCheckoutElement(key, Component)` already exists for built-in keys
([[0003-transport-projections]] §E). This spec promotes it to a **public, runtime-stable contract** and
makes it the single way any party — built-in, first-party gateway, third-party, or host app — supplies a
component. Server-side an element/method is contributed with `Checkout::add(...)` /
`Payment::registerMethod(...)`; client-side its component is contributed with `registerCheckoutElement`
from a published chunk. The `component()` string on the server element is the join key. No server
string is ever turned into a component path by reflection — the client owns the key→component map, the
server owns only the key (the fixed-allowlist rule of [[0002-payment-methods-and-driver]] §B holds).

### B. One shared Vue + SDK — the load-bearing piece (window globals + externals)

A contributed chunk must **not** bundle its own copy of Vue. Two Vue runtimes break reactivity and, fatally,
break `provide/inject` across the boundary — so `useCheckout()` ([[0003-transport-projections]] §F) would
return nothing in a gateway component. The app therefore exposes its **single** Vue instance *and* its
checkout SDK as **runtime globals**, and chunks build against them as **externals**:

```js
// the checkout app's own entry (app.js) — runs before any chunk loads
import * as Vue from 'vue'
window.Vue   = Vue
window.Lunar = { registerCheckoutElement, useCheckout, registerPaymentMethod, /* …SDK… */ }
```

```js
// a contributed chunk, built with `vue` + `@lunarphp/checkout` EXTERNALISED
import { useCheckout } from '@lunarphp/checkout'  // ← preset rewrites to window.Lunar
import { ref }        from 'vue'                  // ← preset rewrites to window.Vue
```

- The app's bundle assigns `window.Vue` (the one runtime) and `window.Lunar` (the SDK entry:
  `registerCheckoutElement`, `useCheckout`, `registerPaymentMethod`, deferred-prop helpers).
  For `registerPaymentMethod` the top-level `window.Lunar.registerPaymentMethod` form is
  **canonical** — registration happens at module load, outside any component setup —
  `useCheckout()` re-exports it only as a convenience.
- The build preset (§D) rewrites every `import … from 'vue'` / `… from '@lunarphp/checkout'` in a chunk
  into references to those globals — so the gateway component instantiates inside the app's component
  tree, shares the one reactive runtime, and `inject`s the `CheckoutProvider` exactly like a built-in
  element.
- **No import map and no `es-module-shims`.** Chunks are plain ES modules whose externals resolve to
  globals already on `window`. This is the mechanism **Statamic's Control Panel uses** for addon Vue
  components — proven, lower-surface, and CSP-clean: a same-origin `<script type="module" src>`, no inline
  import map.

Ordering is guaranteed: chunks load as module scripts **after** the app bundle (§A), so `window.Vue` /
`window.Lunar` already exist when a chunk runs; the registry is reactive, so a chunk that registers late
re-renders its element in place. This is the crux of the design — get the single-shared-runtime right once
and every extension composes.

### C. Delivering the chunk same-origin — registration is the only required step

A contributed chunk must load **same-origin** (so a strict `script-src 'self'` holds, §G). Crucially,
none of the ways to achieve that touch the checkout app's own source/assets — they expose the
*contributor's* chunk, not `lunar/checkout`'s `dist/`. **One registration call** is the whole job
(mirroring Statamic's `registerScript`/`registerVite`, which publish *and* register in a single call):

```php
// gateway package service provider boot()
CheckoutAssets::register(
    package: 'lunar-stripe',
    source:  __DIR__.'/../resources/dist',   // the package's OWN prebuilt chunk dir
    entry:   'checkout.js',                  // the self-registering ES module
    compat:  '^1.0',                         // SDK compat range (§E)
    hot:     __DIR__.'/../resources/dist/hot', // optional: Vite dev-server hot file (§C.3)
);
```

This registers the chunk for same-origin delivery, version-hashed for cache-busting, and emits its
module `<script>`. Three delivery modes back it, none touching the checkout app's own assets:

- **C.1 Served by the checkout asset route (zero publish — the default).** The checkout app exposes a
  same-origin route, `GET /checkout/assets/{package}/{file}`, that streams a *registered* chunk
  straight from the contributing package's `resources/dist` with a far-future immutable cache header.
  Contributing an element then needs **no `vendor:publish` at all** — registration in PHP is
  sufficient and the asset is served same-origin by Lunar. (Only *registered* packages + filenames
  are servable — no arbitrary path read; §G.)
- **C.2 Published to `public/` (opt-in).** For merchants who prefer a static, CDN-frontable `public/`
  or want to pin/fingerprint an asset, `CheckoutAssets::register` also exposes a per-package publish
  tag (`vendor/lunar-stripe`) so `php artisan vendor:publish` copies it. This is the contributor's
  *small* chunk — still **not** the checkout app source.
- **C.3 Vite dev server (HMR — local development of the chunk).** When the registered `hot` file
  exists (its package's Vite dev server is running), the root view emits the chunk's `<script>`
  pointing at that dev server plus the Vite client, instead of the prebuilt file — so an author gets
  **hot module replacement on their element/gateway component** while developing, exactly as Statamic's
  `registerVite` hot-file does. This is dev-only (the dev server is a different origin, so the strict
  `script-src 'self'` is a production posture, relaxed in local dev); production always falls back to
  the prebuilt chunk (C.1/C.2). Absence of the hot file ⇒ prebuilt path; there is no production HMR.

**The host app is a first-class contributor.** A merchant writing a **bespoke** element just for their
store — not a distributable package — builds a chunk with the same preset (§D) and calls
`CheckoutAssets::register(...)` in their `AppServiceProvider`. Their custom element + JS appears in the
checkout with **no app publish and no app rebuild** — exactly the requirement. Crossing into
publish-and-own (tier 3) is needed *only* to rewrite the checkout's own components, never to add one.

`CheckoutAssets` is a build-time registry (bound once in `register()`, Octane-safe — same rule as the
element registry, [[0001-core-element-model]] §G). The checkout root view iterates
`CheckoutAssets::all()` and emits the module tags. A package contributes **both** halves in one
provider: the server descriptor (`Payment::registerMethod` / `Checkout::add`) and the chunk
(`CheckoutAssets::register`). Forgetting the chunk → the element's key renders the dev fallback, a loud
signal, not a silent blank.

### D. A build preset so authors can't get it wrong

The single most common way to break this is a chunk that fails to externalise Vue/the SDK (shipping a
second runtime). Lunar ships a **build preset** — a published Vite config / thin package
(`@lunarphp/checkout-element`) — that an extension author extends:

```js
// vite.config.js in lunar/stripe's checkout UI
import { defineCheckoutElement } from '@lunarphp/checkout-element'
export default defineCheckoutElement({ entry: 'src/checkout.js', name: 'lunar-stripe' })
// → ESM output, `vue` + `@lunarphp/checkout` marked external, hashed filename, manifest
```

The preset guarantees externalised dependencies, an ESM target, and the manifest the PHP side reads. An
author writes a Vue component + a one-line registration; the preset handles the rest.

**Distribution — npm optional, `file:`-vendored by default (the Statamic model).** The SDK + preset are
the only build-time JS Lunar exposes; an author needs them when building a chunk. Two ways to get them,
and npm is **not** required:

- **Vendored `file:` dependency (default).** `lunar/checkout` ships the SDK + preset as a small built
  package *inside* its own `dist/` (e.g. `resources/dist-package`). An extension author's `package.json`
  depends on the vendored copy already on disk via Composer — no separate install, always version-matched
  to the installed `lunar/checkout`:

  ```json
  { "devDependencies": { "@lunarphp/checkout-element": "file:./vendor/lunarphp/checkout/dist-package" } }
  ```

  This is exactly how a Statamic addon depends on `@statamic/cms` (`file:./vendor/statamic/cms/...`) —
  zero npm publishing, no version-skew between the SDK an author builds against and the app that runs.
- **Published to npm (optional).** Lunar *may* also publish `@lunarphp/checkout` + `@lunarphp/checkout-element`
  to npm for authors who prefer a registry dependency. Nice-to-have, not load-bearing.

(The `@lunarphp/checkout` npm question of [[0008-checkout-ui-and-theming]] is resolved here: **the app is
still delivered prebuilt, not via npm; the SDK/preset are vendored on disk, npm-publishing optional**.)

### E. Versioning, compatibility & fallback

The app exposes the SDK version it provides; each chunk declares the SDK range it was built against
(`compat`, §C). On load the SDK checks the range:

- **Compatible** — register normally.
- **Incompatible** — skip registration, log a dev error; the element's key falls through to the dev
  fallback component ([[0003-transport-projections]] §E). The checkout still renders and other elements
  still work — one stale extension never takes down the page.

Adding/removing/renaming an exported SDK symbol is a **breaking change to this contract** and needs a
Rector rule + a major SDK bump (per the package rule). The registry key namespace is first-come with a
collision warning; first-party keys are reserved.

### F. First-party gateways are bundled — install-and-go for the common case

The runtime seam carries the **long tail** (custom + third-party). The **common case** —
Stripe/PayPal/Opayo, all Lunar-maintained — is served by **prebuilding those components into the app's
shipped `dist/`** (behind the same registry, just registered at build time instead of runtime). So a
store using Stripe gets true zero-build install-and-go; a store using a third-party or bespoke gateway
uses the runtime chunk. Same registry, same `component()` contract — only *when* registration happens
differs. This keeps install-and-go true for ~every real store while leaving extension fully open.

### G. Security, CSP & SSR

- **Same-origin assets.** Chunks load same-origin either from the checkout asset route (§C.1) or from
  the merchant's `public/` (§C.2) — so a strict `script-src 'self'` (the hosted page wants one,
  [[0005-hosted-checkout]] §E) is satisfied with no third-party origins and no `'unsafe-inline'`. The
  chunks are plain same-origin `<script type="module">` tags — no inline import map, no `es-module-shims`.
- **The asset route serves only registered chunks.** `GET /checkout/assets/{package}/{file}` resolves
  `{package}`/`{file}` against the `CheckoutAssets` registry and streams only a registered chunk from
  that package's `resources/dist` — never an arbitrary filesystem path from a request value (no path
  traversal, no directory listing).
- **No server-string → path reflection.** The server contributes a *key* and a *registered chunk*; it
  never turns a request value into a component path. Keys stay a fixed allowlist
  ([[0002-payment-methods-and-driver]] §B).
- **Hosted page (future).** A Lunar-operated deployment ([[0005-hosted-checkout]]) only loads chunks for
  the gateways the merchant connected, from a Lunar-controlled origin/allowlist — the same registry,
  asset-allowlisted for the multi-tenant case.
- **SSR.** Registration is client-side; SSR renders the skeleton/fallback for a not-yet-registered key
  and hydrates when the chunk loads. Checkout data is a `no-store` client prop anyway
  ([[0003-transport-projections]] §B), so there is no SSR regression and no PII in cacheable HTML.

## Alternatives considered

- **Rebuild-on-extend** (adding a gateway triggers a rebuild of the app that pulls in its component).
  Rejected as the primary path: since nearly every store installs ≥1 gateway, it would make install-and-go
  apply to almost nobody — the exact benefit [[0008-checkout-ui-and-theming]] exists to deliver. Bundling
  first-party gateways (§F) plus runtime chunks (the proposal) gives zero-build for the common case and
  no-rebuild extension for the rest.
- **Bundle only first-party gateways; everything else forks** (option B alone). Rejected: it closes the
  ecosystem to third-party and bespoke gateway/element UI — directly against the requirement that
  developers ship elements for others to use. §F keeps the first-party bundling as an *optimisation* on
  top of the runtime seam, not the whole answer.
- **Each chunk bundles its own Vue.** Rejected: multiple Vue runtimes break cross-boundary
  `provide/inject` (so `useCheckout()` fails) and bloat the page. The shared-runtime externals (§B) are
  non-negotiable for this design.
- **Module Federation (Vite plugin) for sharing.** Considered; heavier build coupling and a bigger
  author burden than window-global externals. The `window.Vue`/`window.Lunar` externals are the
  lower-surface mechanism (the same one Statamic uses); revisit only if federation features are
  genuinely needed.
- **Import map for sharing Vue/SDK.** Considered and dropped in favour of window-global externals: an
  import map needs the app to publish separate runtime chunks as map targets and an `es-module-shims`
  fallback for the long tail, for no gain over assigning `window.Vue`/`window.Lunar` and externalising
  against them. The global approach is simpler, has no shim dependency, and is battle-tested by Statamic.
- **Web Component / iframe per method.** Rejected for the embedded element seam: loses shared
  reactivity, complicates a11y, and payment SDKs fight iframes. Shadow DOM stays the *isolation* upgrade
  path ([[0008-checkout-ui-and-theming]] §G), orthogonal to *contribution*.
- **Serve chunks from the package vendor path / a CDN (cross-origin).** Rejected as default: cross-origin
  module scripts complicate the strict CSP a payment page wants. Publishing to the merchant's `public/`
  keeps them same-origin.

## Migration impact

- New public surface: `Lunar\Checkout\Support\CheckoutAssets` (the asset registry + its facade), the
  `registerCheckoutElement` / SDK runtime contract promoted to stable, the `@lunarphp/checkout` SDK
  package and the `@lunarphp/checkout-element` build preset — **vendored in the app's `dist/`
  (`dist-package`) and consumed via a `file:` dependency; npm-publishing optional** (§D). All
  net-additive; becomes contract on landing. SDK export changes need a Rector rule + major bump.
- `CheckoutAssets::register` gains an optional `hot` arg (a Vite dev-server hot file, §C.3); when
  present in local dev the root view emits the chunk from the dev server (HMR) instead of the prebuilt
  file. Dev-only; no production change.
- The checkout app's bundle exposes `window.Vue` + `window.Lunar` (its single Vue runtime + the SDK);
  the Inertia root view gains the contributed-chunk `<script type="module">` loop, emitted after the app
  bundle. Both the app bundle and the chunks stream same-origin from package routes (no `vendor:publish`).
- A new same-origin asset route `GET /checkout/assets/{package}/{file}` (§C.1) streams registered
  chunks with no `vendor:publish` step; `CheckoutAssets::register(package, source, entry, compat)`
  registers the chunk and (optionally) a per-package publish tag. Both net-additive.
- [[0002-payment-methods-and-driver]] gains the frontend-contribution half (a `PaymentMethod`'s
  `component()` is now satisfied by a published chunk, not a consumer rebuild); the §C submit seam is
  unchanged.
- First-party gateway packages (`lunar/stripe` reference) ship a prebuilt checkout chunk + the
  `CheckoutAssets::register` call; their server descriptor/driver/webhook are unchanged.
- 16-locale strings: none new here (this is a delivery seam); extension components bring their own.

## Acceptance checks

- `composer require lunar/stripe` (a gateway that registers a checkout chunk) makes its payment
  component render in the checkout app **with no consumer rebuild, no fork, and no publish of the
  checkout app's source/assets** — the chunk loads, self-registers, and `useCheckout()` inside it
  resolves the `CheckoutProvider`.
- A merchant registers a **bespoke** element chunk from their own `AppServiceProvider`
  (`CheckoutAssets::register`) and it renders in the checkout with **no app publish and no app
  rebuild** — proving contribution is independent of the publish-and-own tier.
- A registered chunk loads same-origin via the asset route (§C.1) with **no `vendor:publish` run at
  all**; the route refuses an unregistered `{package}`/`{file}` (no path traversal).
- A store using only a first-party bundled gateway renders that gateway with **zero** extra chunks and
  **zero** build (§F).
- A third-party package can ship a non-payment checkout element (e.g. a gift-message field) via the same
  `Checkout::add` + `CheckoutAssets::register` + chunk path, with no consumer rebuild and no app publish.
- A chunk built with a `compat` range incompatible with the running SDK skips registration, logs a dev
  error, and the page still renders (the key shows the dev fallback) — no crash.
- A chunk that fails to externalise Vue is caught by the build preset (or surfaces as a single-shared-
  runtime violation in a contract test), not silently shipped.
- An extension author builds a chunk against the SDK/preset **vendored on disk** (`file:` dependency on
  `vendor/lunarphp/checkout/dist-package`) with **no npm install** — and the build externalises against
  the same SDK version the installed app runs (§D).
- With the chunk's Vite dev server running, editing its component **hot-reloads** in the checkout
  (the `hot` file routes the `<script>` to the dev server, §C.3); with no dev server the prebuilt chunk
  loads — there is no production HMR.
- Under `script-src 'self'` the same-origin app bundle + chunks load and register with no
  `'unsafe-inline'` and no import map.

## Open questions

- Exact SDK surface promoted to the stable contract (`registerCheckoutElement`, `useCheckout`,
  `registerPaymentMethod`, deferred-prop helpers — which else). (Owner: this spec's implementation,
  reconciled with [[0003-transport-projections]] §E/§F.)
- Whether the app discovers contributed assets purely via `CheckoutAssets::register` calls or also via a
  package-manifest convention (auto-discovery). (Owner: this spec's implementation.)
- Registry-key namespacing for third parties (bare strings vs vendor-prefixed `stripe:card`) and how
  collisions resolve. (Owner: design.)
- For the hosted deployment, the asset allowlist/origin model for third-party gateway chunks in a
  multi-tenant context. (Owner: resolve with [[0005-hosted-checkout]].)

## References

- [[0000-overview]] — package map; cross-cutting conventions (container-as-swap-seam, Octane).
- [[0001-core-element-model]] — the server element model + `Checkout::add` registration this mirrors.
- [[0002-payment-methods-and-driver]] — the `PaymentMethod` server descriptor whose `component()` this
  satisfies.
- [[0003-transport-projections]] — the component registry (§E), `CheckoutProvider` / `useCheckout` (§F),
  the dev fallback for unknown keys.
- [[0005-hosted-checkout]] — multi-tenant asset allowlist; strict CSP.
- [[0008-checkout-ui-and-theming]] — the self-contained prebuilt app this extends; the three
  customisation tiers (this is tier 2); the deferred npm-package question, resolved here for the SDK +
  build preset.
</content>
