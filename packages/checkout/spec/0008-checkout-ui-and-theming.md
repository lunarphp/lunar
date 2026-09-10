# 0008 — Checkout UI delivery & theming

- Status: draft (rewritten — supersedes the earlier "embed into the consumer's Vue build" framing)
- Author: Alec Ritson
- Created: 2026-06-05 (rewritten 2026-06-09)
- TODO item: "Checkout Elements — self-contained Inertia checkout app + sandboxed, swappable theme"

## Problem

[[0003-transport-projections]] §B/§E names what `lunar/checkout` ships — `<LunarCheckout>`, the
hybrid registry, `CheckoutProvider`, composables, built-in element components, and an **Inertia
render**. The earlier draft of this spec resolved *how* by **embedding** that UI into the consumer's
own Vue build — ship Vue source, let the consumer's Vite compile it, mount inside the consumer's
Inertia app, no second Vue runtime. That model is wrong and is **abandoned here**:

- It assumes the consumer runs Inertia + Vue + Vite. A headless storefront on React, Next, Astro,
  Blade, or no JS at all cannot embed it — yet those are exactly the consumers a headless checkout
  must serve.
- It couples the checkout's correctness to the consumer's bundler config, Vue version, and reset/CSS
  environment — the style-bleed problem the old §B then spent its length mitigating.
- It makes "swap one component in your build" a supported tier, which only works if the consumer
  *has* our build — circular.

This rewrite picks one lane and commits: **`lunar/checkout` is a self-contained Inertia + Vue
application that Lunar owns end-to-end.** It ships **prebuilt assets**, owns its build, its Inertia
root, and its page resolver, and registers its own route. The consumer's storefront — whatever stack
— links or redirects to it. Inertia/Vue/Vite are an **internal implementation detail** of the
checkout; the consumer needs none of them. This is the Laravel-package convention used by Cashier,
Telescope, Jetstream, and Horizon: working prebuilt defaults out of the box, publish-to-own for
customisation.

This spec defines the self-contained delivery model, the two customisation tiers, the style posture,
and the swappable theme contract. It is the first implementation step for `lunar/checkout`; the
element model, registry, and REST adapter ([[0001-core-element-model]], [[0003-transport-projections]] §A)
layer on later.

## Proposal

### A. Delivery — a self-contained Inertia app, prebuilt and Lunar-owned

The checkout is its **own** Inertia/Vue application, not a guest in the consumer's app.

```
packages/checkout/
  package.json                   # the checkout app's own JS deps + build scripts
  vite.config.js                 # the checkout app's own Vite config (Inertia + Vue plugin)
  resources/
    js/
      app.js                     # the Inertia client boot — createInertiaApp, page resolver
      components/
        LunarCheckout.vue        # split-layout shell (rail + form)
        ...                      # built-in element components + primitives/
      composables/
        useCheckoutTheme.js      # applies theme tokens to the .lunar-checkout root (§D)
      pages/
        Show.vue                 # the Inertia page; renders <LunarCheckout :checkout :theme />
    css/
      checkout.css               # plain Tender CSS, scoped to .lunar-checkout
      tokens.css                 # design tokens on .lunar-checkout; @imported by checkout.css
    views/
      app.blade.php              # the Inertia ROOT view — emits the prebuilt bundle tags (same-origin), <div id=app>
  dist/                          # PREBUILT, version-controlled, shipped in the composer package
    .vite/manifest.json
    assets/checkout-*.js, *.css
```

- **The package owns its build.** `package.json` + `vite.config.js` live in the package. `inertiajs/inertia-laravel`
  becomes a **package dependency** — Inertia is ours now, not borrowed.
- **Prebuilt `dist/` ships in the composer package.** Install-and-go (§B) requires **no Node, no
  Vite, no build** on the consumer side. The `dist/` + its `manifest.json` are the shipped artefact
  (committed, or pulled from a release — §Open); the package's own Blade root resolves hashed
  filenames from that manifest.
- **Assets stream same-origin from the package — no `vendor:publish`.** Rather than copying `dist/`
  into the consumer's `public/`, the package serves its own bundle through a route
  (`GET /checkout/build/{file}`) straight from `dist/`, and contributed chunks through
  `GET /checkout/assets/{package}/{file}` ([[0009-frontend-element-extension]] §C.1). Install-and-go
  therefore needs **no publish step at all**, and everything loads same-origin — a strict
  `script-src 'self'` (the hosted page wants one, [[0005-hosted-checkout]] §E) holds with no
  third-party origins. Only files inside `dist/` / a registered chunk dir are servable (no path
  traversal). A merchant may still publish to `public/` for a CDN front, but it is never required.
- **The package serves its own route, its own Inertia root.** `CheckoutController::show()` returns
  `Inertia::render('Show', ['checkout' => $dto, 'theme' => $theme->tokens()])` against the
  package's **own** root view and page namespace — not the consumer's. Data arrives server-side as
  `no-store` Inertia props, so no PII is baked into cacheable HTML.
- **Components are transport-neutral**: `<LunarCheckout>` takes the `checkout` payload (the
  `CheckoutData` DTO, [[0001-core-element-model]]) and a `theme` token map as props. The same
  components serve the self-hosted route and the future Lunar-hosted deployment (§G) unchanged.

### B. Scenario A — install & go (the default, ~90% of consumers)

```bash
composer require lunar/checkout
```

The package registers `GET /checkout` (and the element-store routes), serves the prebuilt Inertia
app from `dist/`, and works immediately. The consumer's storefront — React, Blade, Next, Astro,
plain HTML — links or redirects a customer to `/checkout`. No publish, no build, no Vite, no Vue on
the consumer side. Re-branding is a `CheckoutTheme` container rebind (§D): colours, radii, fonts —
**still no build**. Installing a payment gateway, or registering a bespoke element of your own,
likewise needs no publish and no build — its UI arrives as a runtime chunk (tier 2,
[[0009-frontend-element-extension]]).

### C. Scenario B — publish & own (full ownership)

Publishing here is **only** for rewriting the checkout's *own* markup/behaviour. Adding your own
checkout element or gateway UI does **not** require it — that is tier 2 (a self-registering chunk into
the unmodified app, [[0009-frontend-element-extension]]) and is fully independent of this tier. A
consumer who needs to change the checkout's own markup/behaviour beyond theming publishes the app
source and takes ownership:

```bash
php artisan vendor:publish --tag=lunar.checkout.source   # resources/js, resources/css, vite.config.js, package.json
php artisan vendor:publish --tag=lunar.checkout.views    # the Inertia root Blade
```

```php
// config/lunar/checkout.php — turn OFF the package route
'routes' => false,
```

The consumer then edits the published Vue components, registers **their own** route + controller (or
Inertia page) pointing at the published source, and **builds the assets themselves**:

```bash
npm install          # in the published checkout app (its own package.json)
npm run build        # its own Vite config — NOT wired into the storefront's bundler
```

The build is deliberately **standalone**: the consumer needs only Node, runs the checkout app's own
Vite, and is not required to integrate it into their storefront's build pipeline. It is a small,
well-documented manual step — the price of full ownership. After publishing, the consumer owns those
files; package upgrades no longer touch them (the standard publish-ownership trade-off).

### D. Theme — a swappable DTO, bound in the container, applied client-side

Theming is a single immutable value object carrying the **semantic** tokens (design-system roles —
`accent`, `bg-page`, `fg-primary`, `border`, radii, fonts — not raw scales). It carries **only
overrides**: the prebuilt `checkout.css` is the single source of truth for default values; the DTO's
properties are nullable and default to `null` = "use the CSS default". No two-sources-of-truth drift
between PHP and CSS; the override surface stays explicit and statically checkable.

```php
namespace Lunar\Checkout\DataObjects;

final class CheckoutTheme
{
    public function __construct(
        public readonly ?string $accent      = null,
        public readonly ?string $accentHover = null,
        public readonly ?string $bgPage      = null,
        public readonly ?string $bgSurface   = null,
        public readonly ?string $fgPrimary   = null,
        public readonly ?string $border      = null,
        public readonly ?string $radiusMd    = null,
        public readonly ?string $radiusLg    = null,
        public readonly ?string $fontSans    = null,
        public readonly ?string $fontMono    = null,
        // … semantic roles only, mirroring tokens.css
    ) {}

    public static function tender(): self { return new self(); }   // all-null = pure CSS defaults

    /** Immutable override; explicit nullable params (no magic variadics). */
    public function with(/* … */): self { /* clone, replacing only non-null args */ }

    /** Validated, sanitized token-name => value map of the SET tokens only. */
    public function tokens(): array { /* see §F; null tokens omitted */ }
}
```

**One accent re-skins everything.** `tokens.css` derives the accent's tints (`--accent-soft`,
`--accent-soft-border`, `--accent-soft-border-strong`, `--accent-ring`) from `--accent` with
`color-mix()`, and `--fg-link`, `--border-focus` and native checkbox/radio `accent-color` follow it,
so `with(accent:, accentHover:, accentPress:)` alone moves a store off the indigo default. The
derived tints stay pinnable (`accentSoft`, `accentSoftBorder`, `accentRing`) when a brand's mix
wants a hand-picked value; `checkout.css` never names an indigo step for an accent role.

**No config keys.** Per the Lunar mandate (config is for values, the container is for substitutions
— [[0000-overview]] §5), the theme is bound in the container. The package binds the default; the
consumer rebinds in their own service provider:

```php
// CheckoutServiceProvider::register() — package default
$this->app->bind(CheckoutTheme::class, fn () => CheckoutTheme::tender());

// consumer's AppServiceProvider — override (no build, no publish)
$this->app->bind(CheckoutTheme::class, fn () =>
    CheckoutTheme::tender()->with(accent: '#DB2777', radiusMd: '4px')
);
```

`CheckoutController::show()` injects `CheckoutTheme` and passes `tokens()` as the `theme` prop. On
the client, `useCheckoutTheme` applies each token to the `.lunar-checkout` root via
`el.style.setProperty('--accent', …)` — no inline `style=""` and no extra `<style>`, so a strict
`style-src` CSP (the hosted deployment requires it, [[0005-hosted-checkout]] §E) is satisfied without
`'unsafe-inline'`. The bundled defaults render immediately; override tokens apply on mount.

Per-channel / per-merchant theming needs no new surface: bind a closure that resolves `CheckoutTheme`
from the current channel. Same seam, runtime-resolved.

**Favicon and tab title (added in run 1 of the master test plan).** `CheckoutTheme::favicon`
is a URL/path validated by the same chokepoint as `logo` and `stylesheet`, projected as
`favicon` and rendered by the root view as `<link rel="icon">`. The tab title is
"Checkout · {merchant}" from `checkout.merchant`, falling back to the app name. Without
either, the checkout's own document showed the browser's blank globe and a bare "Checkout".

### E. Three customisation tiers (light → heavy)

The old four-tier ladder is replaced — "swap one component in *your storefront's* Vite" assumed the
dead embed model. But the *need* it served (add/replace one element without forking everything) is
real, so it returns as a **runtime** tier that does not depend on the consumer having a build:

1. **Theme** — rebind `CheckoutTheme` (§D). No publish, no build. Brand colours/radii/fonts.
2. **Add or replace an element / gateway** — ship a prebuilt ES-module chunk that self-registers its
   Vue component into the app's registry at runtime, over a shared Vue + SDK
   ([[0009-frontend-element-extension]]). **No fork, no consumer rebuild, and — critically — no
   publish of the app's source/assets (tier 3 below).** Tiers 2 and 3 are **orthogonal**: adding your
   own element + JS never requires owning the checkout app. The contributor (a gateway package, or
   the merchant's own `AppServiceProvider` for a bespoke element) registers only its *own* small
   chunk, served same-origin; the prebuilt app is untouched. This is the symmetric frontend half of
   the server-side `Checkout::add` / `Payment::registerMethod` seam.
3. **Publish & own** — `vendor:publish --tag=lunar.checkout.source`, disable the package route,
   register your own, edit the components, run the checkout app's own build (§C). Full control of
   markup/behaviour; you then own those files.

### F. Security — one sanitization chokepoint

Theme values become CSS — emitted to the client and written into the CSSOM via `setProperty`. Even
when values originate from developer code (and especially once a per-channel resolver sources them
from a database), `CheckoutTheme::tokens()` is the single chokepoint and:

- iterates the **fixed allowlist** that is the DTO's own property set (unknown tokens cannot exist),
- validates each value against a strict, per-token-type pattern — colors (`#hex`, `rgb()/rgba()`,
  `hsl()/hsla()`), lengths (number + unit / `0`), or quoted font stacks — and **rejects** any value
  containing `;`, `}`, `<`, `>`, `"`, or `url(`,
- returns only validated tokens; an invalid value throws at resolve time, not at render.

This holds for the default, programmatic overrides, and future DB/channel sources alike.

### G. Style posture — own document, minimal sandboxing

Because the checkout is its **own** Inertia page on its **own** route (not embedded in the consumer's
DOM), the cross-bleed problem the old §B fought is mostly gone: there is no host page sharing the
document. Tokens still live on a `.lunar-checkout` wrapper (not `:root`) and the kit's resets stay
scoped to that wrapper — cheap hygiene that also keeps the components **Shadow-DOM-ready** for the
hosted deployment ([[0005-hosted-checkout]] §E) without further change. There is no Tailwind in the
package (no Preflight), so no global-reset surface to manage.

### H. Hosted deployment is the same app

[[0005-hosted-checkout]] collapses from "a separate architecture" to "Lunar runs Scenario A for you":
the **same** prebuilt self-contained app, served on a Lunar-operated host, resolving a session by
UUID and redirecting to `success_url`/`cancel_url`. It is a deployment mode of this app, not a second
render stack. The earlier "standalone self-mounting bundle" (old §G) is therefore unnecessary — the
self-contained app *is* the standalone.

## Alternatives considered

- **Embed into the consumer's Vue build (the previous draft).** Rejected — the reason for this
  rewrite. Assumes the consumer runs Inertia + Vue + Vite, couples checkout correctness to their
  bundler/reset environment, and makes per-component swap circular. Excludes every non-Inertia
  storefront, which is the majority of a headless audience.
- **Ship Vue source only, no prebuilt `dist/` (consumer always builds).** Rejected: breaks
  install-and-go; every consumer would need Node + a build just to get a working checkout. Prebuilt
  `dist/` shipped in the package is the default; building is opt-in for the publish-and-own tier.
- **Wire the publish-and-own build into the consumer's storefront bundler.** Rejected: re-introduces
  the coupling this rewrite removes. The published app keeps its **own** standalone Vite config; the
  consumer runs it independently with just Node.
- **Self-contained `<script>` bundle dropped into any page (Vue baked, self-mounting).** Folded into
  §H: the self-contained Inertia app already is the standalone, served on its own route. A separate
  drop-in bundle would be a second build target over the same components for no extra reach.
- **Theme via `config('lunar.checkout.theme')`.** Rejected: violates the container-as-swap-seam
  mandate ([[0000-overview]] §5); a `CheckoutTheme` instance is not config-cache-safe and a
  preset-name string is a weaker, stringly-typed seam than binding the object.
- **iframe isolation.** Rejected: total isolation but breaks payment SDKs, autofill, responsive
  sizing, and a11y. Shadow DOM (§G) is the isolation upgrade for the hosted page if/when needed.

## Migration impact

- The package gains its own frontend toolchain: `package.json`, `vite.config.js`, `resources/js/app.js`
  (Inertia boot), the root `app.blade.php`, and a **version-controlled, prebuilt `dist/`**.
  `inertiajs/inertia-laravel` becomes a package dependency.
- New public surface: `Lunar\Checkout\DataObjects\CheckoutTheme`; the `lunar.checkout.source` /
  `lunar.checkout.views` / `lunar.checkout.styles` publish tags; the `config('lunar.checkout.routes')`
  route toggle; the Inertia page name and its `checkout`/`theme` prop contract. All net-additive;
  becomes contract on landing. Adding/removing a `CheckoutTheme` token, or changing the route-toggle
  or publish-tag names, is a breaking change requiring a Rector rule in the `upgrade` package.
- The placeholder `show.blade.php` ([[0000-overview]]) is replaced by the package's Inertia root view
  + the `Show.vue` page; the controller returns a placeholder `CheckoutData` fixture until the element
  model ([[0001]]) lands, so the page renders end-to-end before the backend model exists.
- The build pipeline must ship fresh `dist/` on every UI change so install-and-go consumers always get
  working prebuilt assets — either committed on UI change (CI build-and-commit) or pulled from the
  matching GitHub release by a composer download plugin (the Statamic `composer-dist-plugin` model,
  keeping the package lean). The serving mechanism is resolved (a same-origin package route, §A); only
  *where `dist/` comes from* is the remaining choice (§Open).
- New routes: `GET /checkout/build/{file}` (the app's own bundle) and
  `GET /checkout/assets/{package}/{file}` (contributed chunks, [[0009-frontend-element-extension]]),
  both streaming same-origin from package `dist/`. Net-additive.
- Fonts (Manrope, JetBrains Mono) and Lucide icons load from CDN per the design system; vendoring
  them locally is a follow-up for offline / strict-CSP storefronts.
- 16-locale strings for the checkout UI land with the element components (out of scope here).

## Acceptance checks

- `composer require lunar/checkout` with **no** publish and **no** build serves a working Tender
  checkout at `/checkout`, streamed same-origin from the prebuilt `dist/` (no `public/` copy), for a
  consumer storefront on any stack.
- Rebinding `CheckoutTheme` in a consumer service provider re-brands the checkout (accent, radii,
  fonts) with no asset rebuild and no config change; the override applies via `setProperty` under a
  `style-src` CSP without `'unsafe-inline'`.
- `vendor:publish --tag=lunar.checkout.source` + setting `config('lunar.checkout.routes') => false` +
  registering a consumer route + `npm run build` in the published app yields a fully consumer-owned
  checkout, built with only Node and the app's own Vite config (not the storefront's bundler).
- `CheckoutTheme::tokens()` rejects a value containing `;`, `}`, `<`, `>`, `"`, or `url(` and returns
  only allowlisted tokens.
- Components render from `checkout` + `theme` props with no transport-specific code, so the hosted
  deployment (§H) reuses the same build unchanged.

## Open questions

- Where the prebuilt `dist/` comes from: committed to the repo (CI build-and-commit on UI change) vs
  pulled from the matching GitHub release by a composer download plugin (Statamic's `composer-dist-plugin`
  approach, leaner repo). *How it reaches the browser* is resolved — a same-origin package route (§A),
  not a `public/` publish. (Owner: this spec's implementation.)
- If a consumer's storefront also runs Inertia, confirm the two Inertia apps coexist cleanly (separate
  root element id, separate asset manifest, separate page namespace) with no client-runtime collision.
  (Owner: this spec's implementation — expected fine, each page boot is independent.)
- Whether published source going stale on upgrade warrants a version check / `--force` reminder in a
  console command. (Owner: this spec's implementation.)
- Vendored vs CDN fonts/icons for offline or strict-CSP storefronts. (Owner: follow-up.)
- Exact semantic-token set exposed on `CheckoutTheme` — which roles are themable vs fixed. (Owner:
  this spec's implementation, reconciled with `tokens.css`.)

## References

- [[0000-overview]] — package map; cross-cutting conventions (container-as-swap-seam §5).
- [[0001-core-element-model]] — `CheckoutData` DTO the components consume.
- [[0003-transport-projections]] — `<LunarCheckout>`, the registry, the Inertia render this builds.
- [[0005-hosted-checkout]] — now a deployment mode of this same app (§H); Shadow-DOM isolation path.
- [[0009-frontend-element-extension]] — tier 2: how third-party elements/gateways contribute UI at
  runtime (shared Vue + SDK via `window.Vue`/`window.Lunar` externals, same-origin chunks) without a
  fork or a consumer rebuild.
- Tender Checkout design system — `tokens.css` (canonical tokens), `ui_kits/checkout/` (the JSX kit
  ported to Vue), README (voice, visual foundations, iconography).
</content>
</invoke>
