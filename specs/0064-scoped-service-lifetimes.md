# 0064 — Scoped service lifetimes for long-lived workers

- Status: proposed
- Author: Glenn
- Created: 2026-08-25
- TODO item: Long-lived worker safety — scoped service lifetimes

## Problem

Several core services that memoize per-visitor state are bound as `singleton`, so the state lives for the process, not the request. Classic PHP-FPM masks this — the process dies with the request — but under Octane and long-running queue workers one process serves many requests/jobs, and the memoized state bleeds between them:

| Binding (contract) | Implementation | Memoized state | Failure under a long-lived worker |
| --- | --- | --- | --- |
| `CartSession` | `Managers/CartSessionManager` | `$channel`, `$currency` | One visitor's channel/currency context applies to the next visitor's cart maths. |
| `StorefrontSession` | `Managers/StorefrontSessionManager` | `$customer`, `$customerGroups`, `$channel`, `$currency`, `$region` | Identity bleed: customer A's context (including group-gated pricing/visibility) survives into customer B's request. |
| `DiscountManager` | `Managers/DiscountManager` | `$discounts`, `$channels`, `$customerGroups`, `$applied` | Stale discount list for the worker's lifetime; `$applied` accumulates across requests unless every path calls `resetDiscounts()`. |

Issue #2561 / PR #2603 were an instance of the same class of bug: `CacheInvalidator` was a singleton holding an unflushed per-request buffer, and its first fix registered a dispatcher listener per instance — which, combined with the corrected `scoped` binding, leaked one permanent listener (retaining its dead instance) per request in exactly the environments the fix targeted.

The codebase already carries the correct precedent — `lunar-access-control` and `CacheInvalidator` are bound `scoped` — but nothing states the rule, so each new service makes the lifetime call ad hoc and the failure mode is invisible in local testing.

The remaining singletons are safe: manifests and modifier registries are configured at boot and read thereafter; `TaxManager` / `PaymentManager` are driver registries; `AttributeCache` delegates to the cache repository rather than instance properties; `PricingManager` is already transient (`bind`), which its fluent per-call state requires.

## Proposal

### 1. Rebind the three stateful managers as `scoped`

In `LunarServiceProvider::registerManagers()`, bind `CartSession`, `StorefrontSession`, and `DiscountManager` with `$this->app->scoped(...)` instead of `singleton`. Behaviour within one classic request is identical; Octane flushes scoped instances per request and the queue worker flushes them per job.

### 2. State the lifetime rule as a convention

Added to `CLAUDE.md` (ships with this spec):

- Bind a service `scoped` when it holds per-request or per-visitor state — session context, the current customer/cart, unflushed buffers, applied-discount context.
- `singleton` is only for services that are stateless after boot: registries, manifests, driver managers.
- Never register an event listener from inside a service instance — wire it once in the service provider and resolve the service at dispatch time (the #2603 pattern).
- No mutable static properties for request state.
- When adding a memoizing property to an existing service, check its binding lifetime first.

### 3. Guard the lifetimes with a regression test

`tests/core/Unit/ServiceLifetimesTest.php`: for each contract in the scoped list (`CartSession`, `StorefrontSession`, `DiscountManager`, `CacheInvalidator`, `lunar-access-control`), resolve, `forgetScopedInstances()`, resolve again, and assert a fresh instance. The test doubles as the canonical list of request-stateful services, so the next addition is a conscious, reviewed choice.

## Alternatives considered

- **Ship an Octane recipe** (a flush list for the host's `octane.php`): rejected — every host app must know to apply it, the default stays silently wrong, and it does nothing for queue workers.
- **Reset state via middleware / job middleware**: rejected — reimplements what container lifetimes already provide, and every entry point (HTTP, queue, Artisan) needs its own reset hook.
- **Make the managers stateless** (re-derive from the session/auth on every call): a larger refactor for no additional safety; within-request memoization is legitimate once the lifetime is right.
- **Do nothing**: Octane and Horizon are mainstream deployment targets for the Laravel versions v2 supports; identity bleed is a severity we cannot document our way around.

## Migration impact

- No database changes, no translations, no Filament/admin impact.
- Public contract surface is unchanged; only binding lifetimes move. Per-request behaviour is identical. A downstream process that relied on these singletons persisting across requests/jobs was depending on the bug being fixed here.

## Open questions

- After scoping, `DiscountManager` re-queries the usable-discount list per request/job instead of once per worker. Acceptable now; if it shows up in profiles, the list belongs behind a repository-backed cache like `AttributeCache` (candidate for the spec-0043 cache toolkit follow-ons). Owner: Glenn.
- Whether CI should gain an Octane smoke job. Out of scope for this spec; add to `TODO.md` ideas if wanted.

## References

- Issue #2561 and PR #2603 — `CacheInvalidator` scoped binding and the provider-registered rollback listener.
- PR #2621 (open) — introduces per-instance memoization on `AttributeCache`; review against this spec's lifetime rules.
- [[0016-service-layer-di]] — constructor DI conventions this builds on.
- [[0040-storefront-context]] — the storefront session state being scoped here.
- [[0043-cache-invalidation-and-events]] — the invalidator whose fix motivated the convention.

## Implementation plan

- [x] Slice 0 — lifetime conventions in `CLAUDE.md` (ships with this spec PR).
- [ ] Slice 1 — rebind `CartSession`, `StorefrontSession`, `DiscountManager` as `scoped`; add `ServiceLifetimesTest` guarding the scoped list.
