# 0040 — Storefront context

- Status: implemented
- Author: Glenn Jacobs
- Created: 2026-06-29
- TODO item: `StorefrontContext` for CartSessionManager and other services

## Problem

The selections that frame a storefront interaction — channel, currency, language, customer, customer groups — live only inside `StorefrontSession`, a session-backed manager:

```php
interface StorefrontSession
{
    public function getChannel(): Channel;
    public function getCurrency(): Currency;
    public function getCustomerGroups(): ?Collection;
    public function getCustomer(): ?Customer;
    // (no language — that rides the app locale via translate())
}
```

This couples "what are the buyer's selections" to "there is an HTTP session". Business logic that needs the selections but runs outside a session has nowhere to get them as a unit:

- A queued job re-pricing a catalogue for a market, or rendering an order email in the buyer's locale.
- The storefront API (drafted separately) resolving selections from headers, not a session.
- A test exercising pricing for a given currency + customer group without booting a session.

Today those callers reassemble the pieces by hand, each falling back to its own global default. `PricingManager` defaults to `Currency::getDefault()` and `CustomerGroup::getDefault()` (`PricingManager` lines 160-168); `CartSessionManager` is constructor-injected a "current" `Channel` and `Currency` that auto-wire to their `getDefault()` with no explicit contextual binding; catalogue price display has no single object that says "for this buyer, this currency, these groups, this language". The cart calculation path is the one place that *is* already context-explicit — a `Cart` carries `channel_id`/`currency_id`/`customer_id` and passes explicit currency + customer groups into pricing and tax (`CartLine/GetUnitPrice`, `CalculateTax`) — which shows the shape the rest of the system is missing: an explicit, passable bundle of selections.

There is also no home for **language** as a selection. The session never tracked it; it leans on the Laravel app locale. Once Region ([[0039-region]]) makes language a market default, the selections need somewhere to carry a resolved language alongside currency and channel.

## Proposal

Introduce `StorefrontContext` — an immutable value object that bundles the resolved selections — and make the session and the cart *produce* one, so any business logic can take an explicit context instead of reaching into the session.

### The value object

`Lunar\Core\DataObjects\StorefrontContext`, a `final readonly` class alongside the other DTOs (`PriceValue`, `PricingResponse`). Like them it carries no contract — it is a value, not a swappable service seam.

```php
final readonly class StorefrontContext
{
    public function __construct(
        public Channel $channel,
        public Currency $currency,
        public Language $language,
        public ?Region $region,          // null until 0039 lands; the spine thereafter
        public ?Customer $customer,
        public Collection $customerGroups, // Collection<CustomerGroup>, never empty (default group)
    ) {}

    public function withCurrency(Currency $currency): static { /* clone */ }
    public function withCustomer(?Customer $customer): static { /* clone + re-resolve groups */ }
    // with* helpers for ergonomic overrides; the object stays immutable
}
```

It adds **language** to the selection set for the first time (the unification the session never offered), and reserves a nullable **region** slot so [[0039-region]] populates it without reshaping the DTO.

### Resolving a context

A single resolver owns the default cascade so it is not re-implemented per caller. `Actions\Storefront\ResolveStorefrontContext` (action + `Contracts\Actions\ResolveStorefrontContext`, bound in `ActionServiceProvider`):

```php
$context = $resolve->execute(/* optional overrides: channel, currency, language, customer */);
```

Cascade, per field: explicit override -> (when Region lands) the resolved region's default -> the primitive's own `getDefault()`. Customer groups derive from the customer when present, else the default group. This is the one place the "region first, then global default" logic lives — [[0039-region]] extends *this* resolver, not every call site.

### Producers

Two seams produce a context; both reuse the resolver or their own stored state:

- **`StorefrontSession::context(): StorefrontContext`** — builds from the session's resolved selections (a new method on the `StorefrontSession` contract). The session stays the request-scoped, mutable producer; `setCurrency()` etc. continue to work and a fresh `context()` reflects them.
- **`Cart::context(): StorefrontContext`** — builds from the cart's stored `channel`/`currency`/`customer` (+ `region` once 0039 lands). Makes the "a cart is already a context" fact first-class, so post-creation cart logic and pre-creation browse logic speak the same type.

Non-session callers (API, jobs, tests) skip both and call `ResolveStorefrontContext` directly, or construct the DTO outright.

### Consuming a context

Business logic accepts the DTO by value. A convenience on the pricing seam collapses the common chain:

```php
// today
Pricing::currency($cart->currency)->customerGroups($groups)->for($product)->get();
// with a context
Pricing::using($context)->for($product)->get();   // sets currency + customer groups (+ channel)
```

The existing `currency()`/`customerGroups()` methods stay. Catalogue/browse-time price display (the storefront listing products before a cart exists) routes through `Pricing::using($context)` instead of leaning on `PricingManager`'s global defaults.

### What this deliberately does not touch

The cart calculation pipelines (`CalculateLines`, `GetUnitPrice`, `CalculateTax`) already pass explicit currency + customer groups derived from the `Cart`; they do not read the session and do not need rewiring. `CartSessionManager` keeps stamping a new cart's `channel`/`currency` — sourced from `ResolveStorefrontContext` instead of the auto-wired "current" `Channel`/`Currency`, which also gives that binding a real definition.

## Alternatives considered

- **A `Contracts\StorefrontContext` interface implemented by the DTO, the session and the cart.** Lets logic type-hint "anything context-shaped". Rejected: it clashes in name with the DTO, and Lunar's value objects (`PriceValue`, `PricingResponse`) carry no contract — values are passed by value, not swapped. A `context()` accessor returning the DTO is simpler and gives the same reuse.
- **Region satisfies the need; no separate context.** Considered and rejected in discussion: a `Region` is a stored, shared, market-level config record with no buyer identity (no customer / customer groups) and only *default* currency/language, whereas a context is an ephemeral per-operation snapshot of the *resolved* selections. One region maps to many contexts. The context *composes* a region; it is not a region.
- **Make the session non-session-aware directly.** Push a "no session" mode into `StorefrontSessionManager`. Rejected: overloads the session with two lifecycles; a plain DTO + resolver is cleaner and testable.
- **Do nothing.** Each non-session caller keeps hand-assembling selections against divergent defaults. Rejected: the storefront API and lifecycle jobs make this a near-term, recurring need.

## Migration impact

- **Database migrations:** none — this is in-memory plumbing.
- **Breaking changes to the public contract surface:** additive. `StorefrontSession` contract gains `context()`. New `StorefrontContext` DTO, `ResolveStorefrontContext` action + contract, `Cart::context()`, `Pricing::using()`. The `CartSessionManager` "current channel/currency" source changes from auto-wired `getDefault()` to the resolver — behaviourally identical for a single-market store.
- **Upgrade path for v1.x consumers:** none required; purely additive surface.
- **Translation / locale impact:** none (no user-facing strings).
- **Filament / admin impact:** none.

## Open questions

- **Does the session start tracking language?** Resolved: **context-only for now.** `StorefrontSession` keeps deferring to the app locale; the context carries a resolved language sourced from `Language::getDefault()`. `StorefrontSession` gains `get/setLanguage()` when [[0039-region]] lands and gives language a real default source (the region). No locale-setting behaviour is baked in here.
- **`Pricing::using()` vs `Pricing::for($purchasable, $context)`.** Which ergonomics read best at call sites? Minor; settle in implementation.
- **Where does `ResolveStorefrontContext` read "current" overrides from when no session and no args?** Pure defaults (its cascade bottoms out at `getDefault()`), confirmed — no implicit request inspection.

## References

- TODO: "`StorefrontContext` for CartSessionManager and other services — foundational plumbing, ties into Region."
- Related specs: [[0039-region]] (Region is the default source the resolver cascades through; the context carries the region), [[draft-storefront-api]] (resolves a context from headers rather than a session).
- Current surface: `Contracts/StorefrontSession.php`, `Managers/StorefrontSessionManager.php`, `Managers/CartSessionManager.php`, `Managers/PricingManager.php`, `DataObjects/PriceValue.php`, `DataObjects/PricingResponse.php`, `Models/Cart.php`, `Pipelines/CartLine/GetUnitPrice.php`, `Pipelines/Cart/CalculateTax.php`.

## Implementation plan

- [x] Slice 1 — `StorefrontContext` DTO (channel, currency, language, customer, customerGroups) + `with*` helpers, in `DataObjects/`. Language is nullable (the session never tracked it; falls back to the app locale). The region slot is deferred to [[0039-region]] — it references the as-yet-unbuilt `Region` model and lands there as a trailing optional argument, which does not reshape existing call sites.
- [x] Slice 2 — `ResolveStorefrontContext` action + contract owning the default cascade; `CartSessionManager` sources new-cart channel/currency from it.
- [x] Slice 3 — `StorefrontSession::context()` on the contract + manager; session produces a context from its selections (delegating to the resolver, honouring an explicitly set customer group via a `customerGroups` override on the resolver).
- [x] Slice 4 — `Cart::context()` producer from stored selections (adds the missing `channel()` relation).
- [x] Slice 5 — `Pricing::using($context)` convenience; `HasPrices::pricing()` takes an optional context for browse-time display. `GetUnitPrice` is left as-is (its user/customer group semantics differ from the context derivation).
- [x] Slice 6 — test proving a context built without a session prices a purchasable through the browse seam.
