# 0091 — PricingManager consumes the storefront context

- Status: proposed
- Author: Glenn Jacobs
- Created: 2026-09-18
- TODO item: PricingManager consumes `StorefrontContext` — one source of pricing state, visible to pricing pipelines (spec 0091)

## Problem

`PricingManager::get()` is the single point every price resolves through: storefront display (`HasPrices::pricing()`), cart lines (`Pipelines/CartLine/GetUnitPrice`, `Actions/Carts/CalculateLineSubtotal`) and table-rate shipping (`FlatRate`, `ShipBy`). It is also the extension point — `config('lunar.pricing.pipelines')` hands each pipeline class the manager after matching so it can replace the matched price.

That extension point cannot see who or where it is pricing for. Spec 0040 added `using(StorefrontContext $context)`, but the manager unpacks the context and throws most of it away:

```php
public function using(StorefrontContext $context)
{
    $this->currency = $context->currency;
    $this->customerGroups = $context->customerGroups;
    $this->userResolved = true;

    return $this;
}
```

`$context->customer`, `->region` and `->channel` never reach a pipeline. Anything keyed on them — contract pricing per customer, price lists per region, per-channel pricing — cannot be built as a pipeline class today. A consumer has to subclass the manager, rebind the contract and replace `GetUnitPrice` to smuggle the values through.

Around that gap sit five related defects, all caused by the manager holding its state as loose properties instead of one context.

**1. State leaks between calls.** The `Pricing` facade caches its resolved instance, so every facade call in a process shares one manager. `reset()` clears only `qty` and `customerGroups`; `currency`, `user` and `userResolved` survive. Confirmed against the host app (default currency USD):

```php
Pricing::for($variant)->currency($gbp)->get();   // GBP
Pricing::for($variant)->get();                   // GBP — expected USD
```

`reset()` also runs only on success. `get()` throws `MissingCurrencyPriceException` before reaching it, and `ShipBy` catches that exception and carries on, leaving `qty` and `customerGroups` set for the next caller.

**2. The leak is load-bearing.** `FlatRate` prices a shipping rate with no currency at all — `Pricing::for($shippingRate)->qty($subTotal)->get()`. It returns the right currency for a non-default-currency cart only because `GetUnitPrice` set the cart's currency on the shared instance moments earlier. Fixing the leak without passing the cart's context to the shipping drivers would break multi-currency flat-rate shipping.

**3. A second default cascade.** `get()` falls back to `Currency::getDefault()` and `CustomerGroup::getDefault()`, and derives customer groups by walking `$user->customers`. `ResolveStorefrontContext` documents itself as "the single home for the storefront default cascade"; pricing does not use it. The two cascades disagree: the resolver's default currency is the default region's, the manager's is `Currency::getDefault()`.

**4. The auth user overrides explicit input.** When a user is resolved — implicitly, from the auth guard — and has customers, `get()` replaces `$this->customerGroups` with the groups of *all* that user's customers, even when the caller passed groups explicitly. The cart path passes `$cart->customer`'s groups via `customerGroups()`, so a logged-in user with two customers gets both customers' groups applied to a cart that belongs to one of them.

**5. `Cart::context()` drops the cart's region, and nothing uses it.** It calls the resolver with `channel`, `currency` and `customer` only, so the context's region is always `Region::getDefault()`, not `$cart->region`. Spec 0040 added it so "cart logic and pre-cart browse logic consume the same explicit type", but no first-party code calls it — the cart pricing path still assembles currency and customer groups by hand.

Two smaller contract problems ride along: `Contracts\PricingManager` declares neither `get()` nor `guest()`, and `HasPrices::pricing()` is return-typed to the concrete `Managers\PricingManager`, so a consumer's replacement must extend the concrete class or hit a `TypeError`.

## Proposal

The manager holds a `StorefrontContext` as its only source of pricing state. The fluent setters become sugar over it, pipelines read it through the contract, and every call starts from a clean manager.

### State

`PricingManager` keeps three pieces of per-call state:

- `context` — `?StorefrontContext`, null until supplied or resolved.
- `purchasable` — as today.
- `qty` — as today.

The `currency`, `customerGroups` and `userResolved` properties are removed as stored state. `user` survives only as a nullable value set by the deprecated `user()` shim, so pipelines that read it keep working; it is never resolved from the auth guard. `get()` wraps its body in `try`/`finally` and clears all of it in the `finally`, so no state survives a call whether it returns or throws. Because nothing is held between calls, the binding stays `bind` and the manager does not join `ServiceLifetimesTest`'s scoped list.

### Supplying a context

```php
$variant->pricing($context)->qty(5)->get();
Pricing::for($variant)->using($context)->get();
```

`using()` stores the context whole. The existing setters stay and delegate:

| Setter                          | Becomes                                                        |
| ------------------------------- | -------------------------------------------------------------- |
| `currency($currency)`           | `context->withCurrency($currency)`                             |
| `customerGroups($groups)`       | `context->withCustomerGroups($groups)`                         |
| `customerGroup($group)`         | `context->withCustomerGroups(collect([$group]))`               |
| `customer($customer)` *(new)*   | re-resolve through `ResolvesStorefrontContext` with that customer, so its groups are derived |

A setter called before any context exists starts from the default context (below). `customer()` goes through the resolver rather than `withCustomer()` because deriving groups from a customer is the resolver's job, per spec 0040.

`user()` and `guest()` remain as `@deprecated` shims so v1 call sites keep working:

- `guest()` → `customer(null)`.
- `user($user)` → the merged customer groups of the user's customers, as today. `context->customer` is set only when the user has exactly one customer; with several it stays null, because a context names one customer and the manager will not guess.

### The default context

The manager never consults the session or the auth guard. Session state is `StorefrontSession`'s concern; it produces a context, and pricing consumes one. The dependency points one way: session → context → pricing.

When `get()` runs with no context supplied, the manager asks a new scoped service, `Contracts\DefaultStorefrontContext`, for one:

```php
interface DefaultStorefrontContext
{
    /** @param  Closure(): StorefrontContext  $resolver */
    public function resolveUsing(Closure $resolver): void;

    public function hasResolver(): bool;

    public function get(): StorefrontContext;
}
```

`get()` returns the registered resolver's context, or — when none is registered — store defaults from `ResolvesStorefrontContext::execute()` with no arguments: the default region, its channel, currency and language, the default customer group, and a null customer. The manager injects `DefaultStorefrontContext` and `ResolvesStorefrontContext` (for `customer()`), replacing the `AuthFactory` dependency, and its own `Currency::getDefault()` / `CustomerGroup::getDefault()` cascade goes.

The service is bound `scoped` and joins `ServiceLifetimesTest`: a registered resolver is per-request state, flushed when the request or job ends, so nothing carries between Octane requests or queue jobs. A static switch on the manager would not be — set by one request's middleware, it would survive in the worker and apply to a later panel or API request.

Explicit input is never overridden. A supplied context always beats the default, and the "auth user's groups replace the supplied groups" branch in `get()` is removed.

### Using the session as the default

The glue between the session and the default lives on the session side. `Contracts\StorefrontSession` gains one method:

```php
StorefrontSession::useAsDefaultContext();

// StorefrontSessionManager
public function useAsDefaultContext(): static
{
    $this->defaultContext->resolveUsing(fn () => $this->context());

    return $this;
}
```

For this to price a logged-in customer correctly, the session's context has to carry that customer's groups — and today it does not. `initCustomerGroups()` stores the default group *and persists it to the session*, so `context()` always passes a non-empty `customerGroups` override and the resolver never derives groups from the customer. Spec 0040 intended otherwise ("honours an explicitly set customer group rather than re-deriving"). The session therefore tracks whether its groups were chosen — set through `setCustomerGroups()` or restored from the session — or merely defaulted. A defaulted group is no longer persisted, and `context()` passes the override only for chosen groups, so an unchosen session derives groups from its customer.

`StorefrontSessionManager` injects `DefaultStorefrontContext` on its constructor. The dependency still points one way — the session knows the context seam; pricing knows neither the session nor who registered the resolver.

Core stops there. It ships no middleware and gains no HTTP layer: *when* a request nominates the session is the application's decision, and different applications answer it differently — a session storefront calls `useAsDefaultContext()`, a token-based storefront API registers its own resolver through `resolveUsing()`, a back-office app registers nothing. The documentation shows the storefront case, and storefront starter kits ship it:

```php
class UseSessionStorefrontContext
{
    public function handle(Request $request, Closure $next): mixed
    {
        StorefrontSession::useAsDefaultContext();

        return $next($request);
    }
}

// bootstrap/app.php
$middleware->web(append: [UseSessionStorefrontContext::class]);
```

With that in place a storefront prices with bare calls — `$variant->pricing()->get()` — and every view, component and controller gets the current visitor's currency, region, customer and groups without passing anything.

- The closure is evaluated on each bare `get()`, not when `useAsDefaultContext()` is called. A login or currency switch earlier in the same request is seen, and ordering relative to auth does not matter. A request that prices nothing pays nothing beyond constructing the session, which a storefront request does anyway.
- It must be called **once per request**. The default is scoped, so a call from a service provider's `boot()` works until the first Octane scope flush and then silently stops. The documentation says so.
- It must run for Livewire update requests as well as the initial render, or prices change between them. Those requests pass through the `web` group, but Livewire re-applies only a fixed list of route-level middleware. The documentation therefore recommends the `web` group, and names `Livewire::addPersistentMiddleware()` for storefronts that apply it per route.
- A storefront that never calls it gets store defaults from a bare call — default-group prices for every visitor — and prices for the visitor by passing `StorefrontSession::context()` explicitly. Strict mode is what tells the two situations apart during development.

Outside a request nothing registers a resolver, so a bare call in a queue job, a console command or a scheduled task resolves store defaults — the same as today. Those callers pass a context explicitly: from the model in hand (`$cart->context()`, which cart calculation always does) or built with `ResolvesStorefrontContext`.

### Strict mode

`Pricing::preventDefaultContext()` makes `get()` throw `MissingStorefrontContextException` when no context was supplied through `using()` or `pricing($context)` **and** no resolver is registered for the scope — the individual setters do not count, since `currency($c)` alone still forgets the customer. It follows the `Model::preventLazyLoading()` precedent ([[0011-prevent-lazy-loading]]). Off by default; a store enables it outside production from a service provider. The exception message names both remedies — pass a context, or call `StorefrontSession::useAsDefaultContext()` for the request. Requests that nominated the session are unaffected; what it finds is the job, command or route that prices against store defaults by accident. It is boot-time configuration, not request state.

### Matching

Unchanged. `get()` reads `context->currency` and `context->customerGroups` where it reads the loose properties today. The precedence of base price, customer-group price and quantity breaks is untouched — changing it belongs to a price-list spec, not this one.

### Reading the context from a pipeline

`Contracts\PricingManager` gains:

```php
public function getContext(): StorefrontContext;
public function getPurchasable(): Purchasable;
public function getQuantity(): int;
public function customer(?Customer $customer);
public function guest();
public function get();
```

A pipeline class types against the contract and reads everything it needs:

```php
public function handle(PricingManager $pricing, Closure $next)
{
    $region = $pricing->getContext()->region;
    $customer = $pricing->getContext()->customer;
    // look up a list price, replace $pricing->pricing, ...
    return $next($pricing);
}
```

`getContext()` is only meaningful inside `get()` — that is, inside a pipeline. The context is resolved before the pipeline runs and cleared after.

The mutable `pricing` property (the `PricingResponse` a pipeline replaces) stays public and as it is.

Pipelines written against the old public properties keep reading them. `currency`, `customerGroups` and `user` become get-only virtual properties (PHP 8.4 property hooks; core already requires `^8.4`) that delegate to the context — `currency` → `context->currency`, `customerGroups` → `context->customerGroups`, `user` → the user last given to `user()`, otherwise null. Writing to them is an error; the setters are the write path.

### Cart and shipping callers

Every first-party caller passes the cart's context, so region and customer reach the pipelines from the cart as well as the storefront.

- `Cart::context()` passes `region: $this->region`. For a cart with a user but no customer it also passes the merged groups of the user's customers, preserving how such carts price today.
- `GetUnitPrice` injects the manager contract and prices with `using($cart->context())`. It cannot call `$purchasable->pricing()`: that lives on the `HasPrices` trait, not the `Purchasable` contract.
- `CalculateLineSubtotal` prices with `$cart->context()->withCustomerGroups($customerGroups)`. Its `execute(CartLine, Collection $customerGroups)` signature, and `CalculateLine`'s, are unchanged — they are action contracts, and narrowing them is a separate change.
- `FlatRate` and `ShipBy` pass `$cart->context()`. `FlatRate` stops depending on leaked currency state; `ShipBy` drops its hand-rolled group lookup.
- The cart's context is resolved once per calculation rather than once per line: `calculate()` holds it on the cart instance for the duration of the pipeline and clears it in a `finally`, and `context()` returns the held value while it is set. Outside a calculation `context()` always resolves afresh, so a changed customer or currency is never served stale.

`HasPrices::pricing()` and the `Pricing` facade docblocks are retyped to `Contracts\PricingManager`.

### What this deliberately does not touch

- The `prices` table, the `Price` model and `PricingResponse`.
- Price matching precedence.
- Price lists, contract pricing or regional pricing themselves. This spec makes them buildable as a pipeline class; a follow-on spec proposes first-party storage.
- The `CalculatesLine` / `CalculatesLineSubtotal` action signatures.

## Alternatives considered

- **Add `customer` and `region` properties beside the existing ones.** Smallest diff, and it would unblock pipelines. Rejected: it grows the loose state that causes the leak, leaves the duplicate default cascade in place, and needs another property the next time the context gains a field. Holding the context means new context fields reach pipelines with no manager change.
- **Pass the context to `get($context)` instead of holding it.** Makes the call stateless by construction. Rejected: it breaks every existing fluent call site for no gain over clearing state in `finally`, and the builder style is what spec 0040 settled on with `using()`.
- **Make the manager `scoped` and keep the state.** Fixes cross-request leakage under Octane but not the within-request leak between a cart line and a shipping driver, which is the one observed.
- **Have the manager fall back to `StorefrontSession::context()` itself.** Rejected: pricing should not know the session exists — spec 0040 made pricing session-free on purpose, and nothing else in core depends on `StorefrontSession` — and a bare call would silently depend on whether a session happens to be started. `useAsDefaultContext()` gives the same ergonomics with the decision made by the application, per request.
- **A static `PricingManager::useSessionContext()` switched on per request.** Same ergonomics as the proposal, but the switch is per-request state in a static: under Octane it outlives the request that set it. It is also session-specific, so a session-less storefront API would need a second switch. The scoped `DefaultStorefrontContext` covers both.
- **Core ships the `UseSessionStorefrontContext` middleware, registered on `web` or merely available.** Registered, it is zero-touch for storefronts and the v1 upgrade — but core applies no middleware to a host app today, the `web` group also serves the panel, the admin and whatever else the app routes through it, and a default that changes what a bare call returns should be visible in the app's own bootstrap. Merely available, it is five lines that give core its first `Http/` folder, for one of several ways of calling the seam. `useAsDefaultContext()` gives the docs, the upgrade step and the strict-mode message a tested, named call to point at without either.
- **No default at all — always pass `StorefrontSession::context()` explicitly.** Simplest for core, and the strictest. Rejected: every price in a storefront codebase becomes a place to forget the context, and forgetting is silent — logged-in customers see retail prices.
- **A per-call opt-in, `Pricing::for($variant)->useSession()->get()`.** Does not solve forgetting — it is still one call per price — and needs the manager to inject `StorefrontSession`. Superseded by `useAsDefaultContext()`.
- **Remove `user()` and `guest()` outright.** Cleaner, but `user($user)` has no mechanical rewrite — a user can own several customers — so no Rector rule can carry v1 call sites across. Deprecated shims cost little.
- **Do nothing; document the subclass-and-rebind route.** It works, but it requires replacing a core cart pipeline step and leaves the leak, which is a correctness bug independent of extensibility.

## Migration impact

- **Database migrations required:** none.
- **Breaking changes to the public contract surface:**
  - `Contracts\PricingManager` gains `getContext()`, `getPurchasable()`, `getQuantity()`, `customer()`, `guest()` and `get()`. A class implementing the contract directly, rather than extending the manager, must add them.
  - `PricingManager`'s constructor takes `DefaultStorefrontContext` and `ResolvesStorefrontContext` instead of `AuthFactory`. Affects subclasses that override the constructor.
  - `Contracts\StorefrontSession` gains `useAsDefaultContext()`, and `StorefrontSessionManager`'s constructor gains `DefaultStorefrontContext`. Affects classes implementing the contract directly and subclasses that override the constructor.
  - New public surface: `Contracts\DefaultStorefrontContext` (scoped), `Pricing::preventDefaultContext()` and `MissingStorefrontContextException`.
  - `currency`, `customerGroups` and `user` become get-only. Reads are unaffected; direct writes (`$manager->currency = ...`) error and must use the setters.
  - `HasPrices::pricing()` returns the contract type.
- **Behaviour changes:**
  - No state carries between calls. Code that configured the manager once and called `get()` in a loop — relying on the currency leak — must pass the context on each call: `$variant->pricing($context)->get()`.
  - **A bare call prices for the current visitor only in a request that called `useAsDefaultContext()`.** Until a storefront does, a bare call returns store defaults — default-group prices for every visitor — where today it applies the auth user's customer groups. This is the change most likely to bite on upgrade, because it is silent. Once the session is nominated, a bare call also follows the session's currency and region, which today it ignores.
  - Explicitly supplied customer groups are no longer overridden by the auth user's. Carts owned by one of a user's several customers price with that customer's groups only.
  - With the session nominated, a bare call prices a user who has several customers with the session customer's groups — `latestCustomer()` unless one was chosen — not the merged groups of all of them.
  - A bare call needs a default channel. A `StorefrontContext` always names one — it is a store invariant, and the installer creates it — but the old cascade never touched channels, so a bare call in a store (or a test) with no default channel now fails where it used to price.
  - The session no longer persists a defaulted customer group, and its context derives groups from the session's customer unless groups were explicitly chosen. Sessions that already hold the persisted default keep treating it as chosen until they expire.
  - `Cart::context()` reports the cart's own region rather than the default region. It has no first-party callers today, so nothing in core changes; consumer code reading it does.
- **Upgrade path for v1.x consumers:** a middleware calling `StorefrontSession::useAsDefaultContext()` restores v1 behaviour for bare storefront calls, so there is no call-site rewrite. Creating a middleware and editing `bootstrap/app.php` is not something a Rector rule does reliably, so `lunar:upgrade` reports it under "Manual actions required" (`StepReport::record()`'s `$manualActions`) with a link to the documented class, and the upgrade guide leads with it. `user()`, `guest()`, the fluent setters and property reads keep working. `WHATS-NEW.md` covers the bare-call change, the loop pattern and the direct-write removal; the monorepo holds no separate upgrade guide. The existing class-rename entries for `PricingManager` in `LunarSetList` are unaffected.
- **Translation / locale impact:** none.
- **Filament / admin impact:** none. The admin and panel pricing screens edit `Price` rows and never resolve prices through the manager.

## Open questions

- **Naming.** `DefaultStorefrontContext`, `useAsDefaultContext()` and `preventDefaultContext()` are working names. Settle in review.
- **Strict mode in this spec or the price-list spec?** Proposed here. With nothing registered by default, strict mode is the only thing that turns a missed `useAsDefaultContext()` — or a job pricing against store defaults by accident — into an error instead of a wrong price. Owner: Glenn.
- **Property hooks for BC reads.** Core requires PHP 8.4 but uses no property hooks yet. If introducing them here is unwelcome, the fallback is to drop the three public properties and add a Rector rule rewriting reads to `getContext()`. Owner: Glenn.
- **Should `CalculatesLine` / `CalculatesLineSubtotal` lose the `$customerGroups` parameter?** Once they price from the cart's context the parameter is redundant, but removing it breaks two action contracts. Proposed: leave for a follow-up. Settle in review.
- **Where the per-calculation cart context is memoized.** Resolved in implementation: on the cart instance, for the life of a `calculate()` call.

## References

- [[0040-storefront-context]] — introduced `StorefrontContext`, the resolver and `Pricing::using()`.
- [[0039-region]] — introduced `Region` and `Cart::region_id`.
- [[0016-service-layer-di]], [[0064-scoped-service-lifetimes]] — constructor injection and service lifetime rules this follows.
- Current surface: `Managers/PricingManager.php`, `Contracts/PricingManager.php`, `Facades/Pricing.php`, `Models/Concerns/HasPrices.php`, `Models/Cart.php` (`context()`), `Pipelines/CartLine/GetUnitPrice.php`, `Actions/Carts/CalculateLineSubtotal.php`, `Actions/Storefront/ResolveStorefrontContext.php`, `Managers/StorefrontSessionManager.php`, and in `table-rate-shipping`: `Drivers/ShippingMethods/FlatRate.php`, `Drivers/ShippingMethods/ShipBy.php`.
- Tests: `tests/core/Unit/Managers/PricingManagerTest.php`, `tests/core/Feature/Storefront/PriceWithoutSessionTest.php`, `tests/core/Stubs/TestPricingPipeline.php`.

## Implementation plan

Ordered so the leak is never fixed before the callers that depend on it are.

- [x] Slice 1 — Callers pass a context. `Cart::context()` passes the cart's region; `GetUnitPrice`, `CalculateLineSubtotal`, `FlatRate` and `ShipBy` price through `$cart->context()`. Adds a multi-currency flat-rate shipping test that fails once the leak is gone unless the context is passed. No manager change.
- [x] Slice 2 — Context as the manager's state. Stored context, setters as sugar, `customer()`, scoped `DefaultStorefrontContext` falling back to store defaults, no session or auth lookup in the manager, strict mode, `try`/`finally` reset, the auth-override branch removed, deprecated `user()`/`guest()` shims, get-only BC properties. Tests: no state survives `get()` on success or on throw; explicit groups are not overridden; a bare call with no resolver returns store defaults and ignores both the auth user and the session (`PriceWithoutSessionTest` covers only an explicit context today); a supplied context beats a registered resolver; the default service is scoped (`ServiceLifetimesTest`); strict mode throws only with no context and no resolver.
- [x] Slice 3 — Contract and pipeline surface. `getContext()`, `getPurchasable()`, `getQuantity()`, `get()`, `guest()`, `customer()` on the contract; `HasPrices::pricing()` and the facade retyped to the contract; per-calculation cart context memoization. Tests: a pipeline stub reads region and customer from both the storefront path and the cart path.
- [x] Slice 4 — Session as the default. `StorefrontSession::useAsDefaultContext()` on the contract, manager and facade docblock. Feature tests over real HTTP requests, using a middleware defined in the test: a bare call follows the logged-in customer, a mid-request currency switch is seen, and a route that does not nominate the session gets store defaults. That the resolver does not survive the scope is asserted at unit level — the test HTTP kernel does not flush scoped instances between requests, so an HTTP-level assertion would prove nothing. Includes the session customer-group fix above.
- [x] Slice 5 — Upgrade and docs. `lunar:upgrade` manual action naming `useAsDefaultContext()`; `WHATS-NEW.md` note covering the middleware, the once-per-request rule, the Livewire placement, the bare-call change, the loop pattern and direct property writes. The docs-site page for the middleware is outside this repo and still to write.
