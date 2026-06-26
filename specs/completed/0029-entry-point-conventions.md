# 0029 — Entry-point conventions: actions, model verbs, managers

- Status: implemented
- Author: Glenn Jacobs
- Created: 2026-06-12
- TODO item: consistency follow-up surfaced in spec 0022 review

## Problem

Lunar exposes domain operations through three different styles, distributed inconsistently:

- **Model verbs** — `$cart->createOrder()`, `$cart->add()`, `$transaction->capture()`. Thin sugar on the model that delegates to the bound action contract (`app(CreatesOrder::class)->execute($cart, …)`), so rebinding the action still takes effect through the verb. Only `Cart` and `Transaction` have these.
- **Manager + facade** — `Fulfilments::ship($fulfilment, $tracking)`. Spec 0022 fronted the fulfilment domain with `FulfilmentManager`, mirroring "how `CartSession` / `Payments` / `Discounts` front their domains".
- **Raw contract resolution at the call site** — `app(CancelsOrder::class)->execute($record, …)` inside the Filament order actions.

The same conceptual thing — "perform an operation on this model" — is therefore spelt three ways depending on which domain you are in. A developer who has internalised `$cart->createOrder()` reaches for `$order->createFulfilment()` and finds nothing.

The managers themselves split into two species. `CartSessionManager` and `StorefrontSessionManager` hold session state; `PricingManager` and `DiscountManager` are fluent context builders (plus, for discounts, a type registry); `PaymentManager` and `TaxManager` are Laravel `Manager` driver resolvers. Each *manages* something that does not belong to a single model instance. `FulfilmentManager` is the odd one out: it holds zero state, and every one of its eleven methods takes the subject model as its first argument and one-line-delegates to an action contract. It is not a manager — it is a stateless router that exists only to give the verbs a home.

This also explains why there is no `CartManager`: the cart's operations are all anchored to a cart instance, so they live on the model, and `CartSession` exists only for the part that genuinely is session state. The cart domain has the layering right; fulfilments deviated from it.

## Proposal

Three layers, each with one job. The test for which layer an operation belongs to is mechanical.

### 1. Actions — the implementation and swap seam (always)

Unchanged from spec 0016. Every operation is an action class with a contract bound in `ActionServiceProvider::$actions`. The action is always the *extension* point and never required to be the *ergonomic* entry point.

### 2. Model verbs — the default public API for model-anchored operations

**If an operation's first parameter is the model it operates on, it is exposed as a verb method on that model — and declared on the model's contract** — as a one-line delegation to the action contract, exactly the `Cart::createOrder()` shape:

```php
public function ship(array $tracking = []): Fulfilment
{
    return app(ShipsFulfilment::class)->execute($this, $tracking);
}
```

Call sites — Filament actions included — use the verb. `app(SomeContract::class)->execute($model, …)` no longer appears in consumer-facing code. Models are not service-layer classes, so the inline `app()` delegation does not conflict with spec 0016's DI rules (the `Cart` model already established this).

Verbs are for the public, consumer-facing operations. Internal orchestration actions — the rollup resolvers, `RecomputeOrderStatus`, `EnsureInitialFulfilment` — stay action-only; not every action needs sugar.

Applied to the current surface:

- **`Order`** gains `createFulfilment(array $lines, array $attributes = [])`, `cancel(?string $reason = null, ?string $note = null, bool $notify = true)`, `close()`, `reopen()`, `capture(int|string $transactionId, float|int|string $amount)` and `refund(int|string $transactionId, float|int|string $amount, ?string $notes = null)` — delegating to `CreatesFulfilment`, `CancelsOrder`, `ClosesOrder`, `ReopensOrder`, `CapturesOrder` and `RefundsOrder`. (`Transaction::capture()/refund()` are a different layer — they call the payment driver directly — and are unchanged.)
- **`Fulfilment`** gains `ship(array $tracking = [])`, `split(array $moves)`, `merge(Collection $sources)`, `moveLinesTo(Fulfilment $to, array $moves)`, `cancel()`, `markReturned()`, `transition(string $state)`, `hold(?string $reason = null, ?string $note = null)`, `release()`, `changeLocation(int $locationId)` and `addTracking(array $attributes)` — delegating to the `Actions/Fulfilment/*` contracts. `markReturned()` rather than `return()`: bare `return` is legal as a PHP method name but reads badly.
- **`FulfilmentTracking`** gains `remove()`, delegating to `RemovesFulfilmentTracking` (it is more than a `delete()` — the seam is swappable).

### 3. Managers + facades — only when there is something to manage

A manager (and its facade) exists only for concerns that do not belong to a single model instance: **session state** (`CartSession`, `StorefrontSession`), **fluent context** (`Pricing`, `Discounts`), **driver resolution** (`Payments`, `Taxes`), **registries** (the manifests, `Carriers`). A manager whose every method forwards a model-first call is layer 2's job wearing a trench coat — do not add one.

**Consequence: `FulfilmentManager`, `Contracts\FulfilmentManager` and `Facades\Fulfilments` are removed.** v2 is pre-release with no consumers; keeping them as aliases would institutionalise the inconsistency on day one of the v2 API. `Carriers` (a registry) stays. The admin component and tests move to the model verbs (`Fulfilments::ship($f, $t)` → `$f->ship($t)`).

The convention is recorded in `CLAUDE.md` so future PRs are reviewed against it.

## Alternatives considered

- **Keep both surfaces (manager/facade + model verbs).** Rejected: two public entry points per operation to document, test and keep in sync, forever — and it leaves "which one do I use?" unanswered, which is the problem this spec exists to solve.
- **Facade-only (drop model verbs, add managers everywhere — `CartManager` et al).** Rejected: it abandons the model-centric idiom (`$cart->createOrder()`) that Lunar deliberately cultivates, and every manager would be the stateless-router species this spec rules out.
- **Mechanical enforcement via an architecture test** (e.g. classes in `Managers/` must declare state or implement a driver/registry contract). Deferred: the heuristics are crude enough to produce noise; the CLAUDE.md convention plus review is proportionate. Revisit if the rule gets violated in practice.

## Migration impact

- **Database**: none.
- **Breaking changes**: `Managers\FulfilmentManager`, `Contracts\FulfilmentManager` and `Facades\Fulfilments` are removed. They have never shipped in a release (introduced on the unmerged spec-0022 branch), so there is no Rector rule and no upgrade-guide entry — spec 0022 §G is amended instead.
- **Public surface added**: the verb methods above, on the models and their `Models\Contracts\*` interfaces. Consumers replacing a model via the `ModelManifest` extend the shipped base models, so they inherit the verbs; anyone implementing the model contracts from scratch must add them.
- **Translations**: none.
- **Filament / admin**: call-site swaps only — the order actions use `$record->cancel()` / `close()` / `reopen()` / `capture()` / `refund()`; the fulfilments component uses the `Fulfilment` verbs.

## Open questions

None.

## References

- [[0016-service-layer-di]] — actions as contract-bound seams; the DI rules model verbs deliberately sit outside.
- [[0022-order-fulfilments]] — §G introduced the manager/facade this spec retires; amended to point here.
- `Cart::createOrder()` (`Models/Cart.php`) — the precedent this spec generalises.
