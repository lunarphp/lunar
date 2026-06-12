# Lunar Checkout

A **self-contained, Lunar-owned Inertia + Vue checkout application**. It ships
**prebuilt**, owns its own build, Inertia root and route, and works the moment
you install it. Your storefront — React, Next, Astro, Blade, plain HTML, or no
JS at all — simply **links or redirects** a customer to `/checkout`. Inertia,
Vue and Vite are an internal implementation detail; your storefront needs none
of them.

This is the Cashier / Telescope / Horizon pattern: working prebuilt defaults out
of the box, publish-to-own when you need full control — and, in between, a
runtime seam to add your own elements and payment gateways **without forking or
rebuilding anything**.

> Design lives in [`spec/`](./spec) — start with [`spec/overview.md`](./spec/overview.md).
> This README is the how-to for the scaffolding those specs describe (0008 + 0009).

---

## 1. Install & go

```bash
composer require lunarphp/checkout
```

That's it. The package registers `GET /checkout`, serves the prebuilt app, and
streams its assets same-origin from the package — **no `npm`, no Vite, no
`vendor:publish`, no build**. Point your storefront's "Checkout" button at
`/checkout`.

---

## 2. The two customisation axes (read this first)

The single most important thing to understand: **adding your own checkout UI and
owning the checkout app are independent.** Do not conflate them.

| You want to… | Tier | Publish the app? | Rebuild the app? |
|---|---|---|---|
| Re-brand (colours, radii, fonts) | 1 — Theme | No | No |
| **Add / replace an element or payment gateway** | **2 — Contribute** | **No** | **No** |
| Rewrite the checkout's own markup/behaviour | 3 — Publish & own | Yes | Yes |

Tiers 1 and 2 leave the prebuilt app **untouched**. You only reach tier 3 to
change Lunar's *own* components — never just to add one of yours.

---

## 3. Tier 1 — Theme (no build)

The theme is an immutable DTO bound in the container. Rebind it in a service
provider; the override applies client-side via CSS custom properties (CSP-safe,
no rebuild).

```php
use Lunar\Checkout\DataObjects\CheckoutTheme;

// AppServiceProvider::register()
$this->app->bind(CheckoutTheme::class, fn () =>
    CheckoutTheme::tender()->with(accent: '#DB2777', radiusMd: '4px')
);
```

Per-channel theming: bind a closure that resolves the theme from the current
channel. Same seam.

---

## 4. Tier 2 — Add your own element or gateway (no publish, no rebuild)

A checkout element has two halves: a **server descriptor** (what data it
captures, where it sits) and a **Vue component** (how it renders). The prebuilt
app can't contain a component it has never heard of — so a contributor ships a
tiny **self-registering chunk** that drops its component into the running app.

### 4a. Register the server half

```php
// your package — or your own app's AppServiceProvider::boot()
Checkout::add(GiftMessageElement::class);          // a CheckoutElement
// or, for a gateway:
Payment::registerMethod(StripeCardMethod::class);  // a PaymentMethod (spec 0002)
```

### 4b. Register the chunk — one call

```php
use Lunar\Checkout\Facades\CheckoutAssets;

CheckoutAssets::register(
    package: 'acme-gift-message',
    source:  __DIR__.'/../resources/dist',  // your OWN prebuilt chunk dir
    entry:   'checkout.js',                 // the self-registering ES module
    compat:  '^1.0',                        // SDK range it was built against
);
```

That's the whole job. The chunk is served **same-origin** from
`/checkout/assets/{package}/{file}` — **no `vendor:publish`** — and its
`<script type="module">` is emitted into the checkout after the app boots.

### 4c. The chunk itself

```js
// resources/js/checkout.js  (built into resources/dist/checkout.js)
import { registerCheckoutElement } from '@lunarphp/checkout'
import GiftMessage from './GiftMessage.vue'

registerCheckoutElement('gift-message', GiftMessage) // key === server component()
```

Inside `GiftMessage.vue` you may call `useCheckout()` exactly like a built-in
element — because every chunk shares the app's **one** Vue runtime and SDK (see
§6). An unknown key renders a dev fallback until its chunk loads; the registry is
reactive, so it appears in place the moment it registers.

### 4d. Build the chunk

Build with the Lunar preset so `vue` and `@lunarphp/checkout` are externalised
(never bundled — that's what keeps a single runtime):

```js
// your package's vite.config.js
import { defineCheckoutElement } from '@lunarphp/checkout-element'
export default defineCheckoutElement({ entry: 'resources/js/checkout.js', name: 'acme-gift-message' })
```

> **The host app is a first-class contributor.** Writing a bespoke element just
> for your own store (not a distributable package)? Do exactly the same from your
> `AppServiceProvider` — build a chunk, `CheckoutAssets::register(...)`. Still no
> app publish, no app rebuild.

First-party gateways (Stripe/PayPal/Opayo) are **prebuilt into the shipped app**,
so the common case is true zero-build install-and-go; the runtime chunk carries
everything else (spec 0009 §F).

---

## 5. Tier 3 — Publish & own (full control)

Only when you need to change the checkout's *own* markup or behaviour:

```bash
php artisan vendor:publish --tag=lunar.checkout.source   # resources/js, resources/css, package.json, vite.config.js
php artisan vendor:publish --tag=lunar.checkout.views    # the Inertia root Blade (optional)
```

```php
// config/lunar/checkout.php — turn OFF the package routes
'routes' => false,
```

Then register your own route + controller pointing at the published app, edit the
Vue components, and build the app's **own** Vite (it is standalone — not wired
into your storefront's bundler; you need only Node):

```bash
cd resources/vendor/lunar-checkout
npm install
npm run build
```

You now own those files; package upgrades no longer touch them.

---

## 6. How assets are served (and the single-runtime rule)

- **Install-and-go needs no publish.** The app's own prebuilt bundle streams from
  `GET /checkout/build/{file}` and contributed chunks from
  `GET /checkout/assets/{package}/{file}` — both **same-origin**, both straight
  from package `dist/`. A strict `script-src 'self'` is satisfied; only
  registered files are servable (no path traversal).
- **One Vue runtime, one SDK.** The app bundle exposes `window.Vue` and
  `window.Lunar` (the SDK). Contributed chunks externalise `vue` /
  `@lunarphp/checkout` to those globals (the build preset does this), so a
  gateway component shares the app's reactivity and `inject`s the same
  `CheckoutProvider`. Two Vue runtimes would silently break `useCheckout()` — the
  preset exists to make that impossible.

---

## 7. The checkout session (spec 0004)

A **checkout session** is the addressable record of an in-progress checkout. It is
created from a cart by a swappable **checkout driver**, carries a public UUID, and
**pins a snapshot** of the cart's currency, channel and totals at the moment
checkout begins — so the amount a customer pays can't drift if the cart changes
underneath them. The default `lunar` driver targets Lunar's cart + order.

You don't build any of this yourself — your storefront just creates a session and
sends the customer to it.

### 7a. Start a checkout (the common case)

Resolve the driver and hand it the current cart. You get back a session with a
`url` — redirect the customer there and the hosted checkout takes over.

```php
use Lunar\Checkout\Contracts\CheckoutDriver;
use Lunar\Core\Facades\CartSession;

class CheckoutController
{
    public function __invoke(CheckoutDriver $checkout)
    {
        $session = $checkout->createSession(CartSession::current());

        return redirect($session->url); // → /checkout/{uuid}
    }
}
```

`createSession()` snapshots the cart **once**. From here the session owns its
amounts (`amount_subtotal`, `amount_total`, in minor units), `currency_code`,
`channel_id` and `locale`; later edits to the shopper's cart never move them. A
re-checkout makes a fresh session (a cart can have many).

### 7b. The session is the integrity anchor

When you take payment, verify the gateway intent against the **session's** pinned
total — never the live cart:

```php
$session = CheckoutSession::where('uuid', $uuid)->firstOrFail();

abort_unless(
    $intent->amount === $session->amount_total,   // pinned minor-unit total
    422,
    'Payment amount no longer matches the checkout.'
);
```

### 7c. Resume a session by its UUID

The `uuid` is the route key — a bearer capability token, unguessable and never the
database `id`. Bind it straight into a route to resume or render a checkout:

```php
use Lunar\Checkout\Models\CheckoutSession;

Route::get('/checkout/{checkoutSession}', function (CheckoutSession $session) {
    abort_if($session->isExpired(), 410);          // terminal sessions aren't operable

    return view('checkout', [
        'total'    => $session->amount_total,
        'currency' => $session->currency_code,
        'status'   => $session->status->label(),   // Open / Completed / Expired …
    ]);
});
```

### 7d. Expiry is the session's own clock

Each session gets an `expires_at` (default 24h — `config('lunar.checkout.session.expires_after')`)
and a lifecycle state. Check it on read with `isExpired()`; a scheduled command
flips stale **Open** sessions to **Expired** (it deliberately leaves
`PaymentProcessing` alone — payment may still be confirming):

```php
$session->isExpired();      // bool — past expires_at, or a terminal Expired state

// runs hourly out of the box; or invoke directly:
php artisan lunar:checkout:expire-sessions
```

### 7e. Pass return URLs & metadata

For hosted flows that bounce back to your storefront, or to stamp your own
correlation data, drive the create-action directly — it accepts caller extras and
echoes them back on the session:

```php
use Lunar\Checkout\Contracts\Actions\CreatesCheckoutSession;

$session = app(CreatesCheckoutSession::class)->execute(CartSession::current(), [
    'success_url'         => route('orders.thanks'),
    'cancel_url'          => route('cart.index'),
    'client_reference_id' => $yourOrderRef,
    'metadata'            => ['gift' => true, 'source' => 'mobile-app'],
]);
```

### 7f. Front a non-Lunar backend

The session, UUID and state machine are backend-neutral; only the driver knows
your cart and order shapes. Register your own and select it by name — no
`config(...class)` swap, the standard Manager `extend()` seam:

```php
// YourServiceProvider::register()
use Lunar\Checkout\Managers\CheckoutSessionManager;

$this->app->make(CheckoutSessionManager::class)
    ->extend('statamic', fn ($app) => new StatamicCheckoutDriver(/* … */));
```

```php
// config/lunar/checkout.php
'driver' => 'statamic',
```

Your driver's `createSession()` ingests whatever you call a cart and `complete()`
finalises it into your own order model (linked via the session's `order` morph) —
everything in between behaves identically.

---

## 8. Developing this package

```bash
npm install
npm run build      # emits the prebuilt dist/ (committed/released artefact)
npm run dev        # Vite dev server, for working on the app's own components
```

`dist/` is what install-and-go consumers run; rebuild it on every UI change.

---

## Key files

| Path | Role |
|---|---|
| `resources/js/app.js` | Inertia client boot; exposes `window.Vue` + `window.Lunar` (spec 0008/0009) |
| `resources/js/index.js` | the `@lunarphp/checkout` SDK surface chunks build against |
| `resources/js/composables/elements.js` | the reactive component registry (`registerCheckoutElement`) |
| `resources/views/app.blade.php` | the Inertia root view — bundle tags + contributed-chunk loop |
| `vite.config.js` / `package.json` | the app's own build toolchain |
| `src/Support/CheckoutAssets.php` | server registry of contributed chunks |
| `src/Support/CheckoutBundle.php` | resolves the app's own prebuilt bundle from `dist/` |
| `src/Http/Controllers/CheckoutController.php` | renders the app; streams bundle + chunks same-origin |
| `config/checkout.php` | route path/middleware, `routes` toggle, `driver` |
