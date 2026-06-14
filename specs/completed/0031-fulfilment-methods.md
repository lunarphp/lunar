# 0031 — Fulfilment methods: pluggable fulfilment flows

- Status: implemented
- Author: Glenn Jacobs
- Created: 2026-06-13
- TODO item: "Fulfilment methods — collection / digital / custom fulfilment flows" (follow-up surfaced implementing spec 0022)

## Problem

Spec [[0022-order-fulfilments]] models a `Fulfilment` as, implicitly, **a shipment**. The vocabulary is hardwired to posting a parcel through a carrier:

- The only terminal-progress verb is `ship()`; the terminal state is `Shipped`; reaching it stamps `shipped_at`; and every fulfilment carries one-to-many `FulfilmentTracking` (carrier + number) from [[0024-shipping-carriers]].
- `ResolveFulfilmentStatus` hardcodes the dispatched states as the string literals `['shipped', 'returned']`.
- `EnsureInitialFulfilment` creates exactly **one** parcel covering every fulfillable line.
- [[0030-fulfillable-order-lines]] keys "needs a fulfilment" on `order_lines.requires_shipping` (stamped from `Purchasable::isShippable()`) — i.e. *needs physical delivery*.

Two real-world flows don't fit, and a third tells us the shape is wrong:

- **Collection / click-and-collect (trade counters).** The goods are physical (`requires_shipping = true`) so a parcel *is* created — but it presents ship-and-track, which is wrong for an in-store pickup. The merchant wants "ready for collection → collected", not a tracking number. The checkout signal already exists — `ShippingOption::collect` (the table-rate `Collection` driver returns `collect: true`) — but it dies at the order boundary; nothing downstream reads it.
- **Digital / manual provisioning.** A licence key, an access grant, a voucher: `isShippable()` is `false`, so `requires_shipping = false`, so **no fulfilment is created at all** and `ResolveFulfilmentStatus` short-circuits the order to `Fulfilled` the moment it's placed — even though a human still has to provision it. There is nowhere to record "I've sent the key" and no lifecycle that says it's outstanding.
- **Anything bespoke.** A pharmacy needs prescription verification before dispatch; a furniture retailer needs a booked delivery slot; a marketplace hands off to a third-party fulfilment service. These need *extra states*, not just relabelled ones.

The tempting fix — a closed `method` enum (`shipping | collection | digital`) on the fulfilment — would make `Fulfilment` the **one** place in core where an extension point is closed. Everywhere else a consumer registers their own implementation against a contract: payment drivers, tax drivers, shipping-rate drivers ([[0024-shipping-carriers]]'s `ShippingCarrier`), the field-type and shipping manifests. Fulfilment flow should be the same: **a registered seam, batteries included.**

## Proposal

Introduce a **fulfilment method** — a registered driver that owns a fulfilment's *flow*: its state graph, which order lines it claims, and whether it carries carrier tracking. A `Fulfilment` stores its method as a key (like a carrier key). Core ships three methods built **on** the seam — `shipping` (today's behaviour, refactored on), `collection`, and `digital` — so core dogfoods it; a consumer registers `prescription` (or anything) the same way core registers `shipping`.

The order-level rollup stays method-agnostic because every fulfilment state declares a **category** from a small fixed vocabulary the rollup understands. That single idea — pluggable per-parcel states, fixed rollup categories — is what keeps arbitrary flows safe.

### A. The fulfilment-method seam

A method is a driver behind a registry, mirroring `CarrierManifest` / `Carriers` exactly (singleton, config-seeded, `register()/all()/get()`).

```php
namespace Lunar\Core\Contracts;

interface FulfilmentMethod
{
    public function getKey(): string;                 // 'shipping'
    public function getLabel(): string;               // translated, for the admin badge

    /** @return list<class-string<FulfilmentState>> the states this flow uses */
    public function states(): array;

    /** @return array<class-string<FulfilmentState>, list<class-string<FulfilmentState>>> */
    public function transitions(): array;

    /** @return class-string<FulfilmentState> initial state on create */
    public function defaultState(): string;

    /** @return class-string<FulfilmentState> the canonical "done" target `fulfil()` advances to */
    public function fulfilledState(): string;

    /**
     * Which of the order's fulfillable lines this method covers, given the
     * order (and its chosen shipping option). Called over a shrinking pool in
     * priority order — see §D.
     *
     * @return Collection<int, OrderLine>
     */
    public function claim(Order $order, Collection $unclaimed): Collection;

    /** Lower runs first; the catch-all `shipping` is highest. */
    public function priority(): int;

    /** Whether parcels of this method carry carrier tracking (§E). */
    public function usesTracking(): bool;
}
```

- **Registry** — `Lunar\Core\Manifests\FulfilmentMethodManifest` (`Contracts\FulfilmentMethodManifest`), bound as a singleton in `LunarServiceProvider::registerServices()` alongside `CarrierManifest`, exposed via `Lunar\Core\Facades\FulfilmentMethods`. `register(FulfilmentMethod|string|array)`, `all(): Collection<string, FulfilmentMethod>` (priority-ordered), `get(?string $key): ?FulfilmentMethod`. On construction it registers every method in `config('lunar.fulfilment.methods')`, then the core three.
- **Core method drivers** live in `Drivers/FulfilmentMethods/` (`Shipping`, `Collection`, `Digital`) — siblings of table-rate-shipping's `Drivers/ShippingMethods/`, reusing the existing `Drivers/` concern rather than a new base folder. (`Shipping` here is a fulfilment-flow driver and must not be confused with the table-rate `ShippingMethod` Eloquent model — hence the namespace.)
- **Storage** — `fulfilments` gains `method` (string, indexed, default `shipping`). `Fulfilment::method(): FulfilmentMethod` resolves it from the manifest, exactly like `FulfilmentTracking::carrier()`. `CreateFulfilment` stamps `method` from its attributes (defaulting to `shipping`) and the state to that method's `defaultState()`.

### B. State categories — the fixed rollup vocabulary

Free-form per-parcel graphs would break `ResolveFulfilmentStatus`, which can't know what `prescription-verified` means. So every `FulfilmentState` declares a **category** from a closed enum the rollup and the parcel mechanics reason over (it revives, for the parcel lifecycle, the grouping idea the headline `OrderStateCategory` carried before 0022 removed it):

```php
namespace Lunar\Core\Enums;

enum FulfilmentStateCategory
{
    case Outstanding;   // not yet handed over: pending, in-progress, prescription-verified, ready-for-collection…
    case Fulfilled;     // gone to the customer: shipped, collected, provisioned…
    case Returned;      // came back
    case Cancelled;     // never counted
}
```

`FulfilmentState` gains `abstract public function category(): FulfilmentStateCategory;`. Core mapping: `Pending`/`InProgress` → `Outstanding`, `Shipped` → `Fulfilled`, `Returned` → `Returned`, `Cancelled` → `Cancelled`.

The manifest aggregates the category map across all registered methods and exposes the **state `$name`s in a category**, so the rollup stays one indexed query rather than per-row PHP:

```php
FulfilmentMethods::stateNamesIn(FulfilmentStateCategory::Fulfilled);
// ['shipped', 'collected', 'provisioned', …]   ← every registered method's Fulfilled-category states
```

**Methods extend within the categories** — a flow starts `Outstanding`, ends in a `Fulfilled`/`Returned`/`Cancelled` terminal, and adds whatever `Outstanding` intermediate steps it needs. It never invents a fifth category. That constraint is what makes the rollup correct by construction and the Spatie reconciliation (§C) tractable.

`shipped_at` generalises from a `Shipped`-specific stamp to "the handed-over timestamp": it is set when a fulfilment transitions **into** a `Fulfilled`-category state and cleared when it leaves one, driven by category, not by the literal `Shipped` class. (Column name kept; it reads as collected-at / provisioned-at for those methods.)

### C. Per-method state graphs over Spatie

`spatie/laravel-model-states` resolves a hierarchy's states/transitions through `FulfilmentState::config()` — a **`static` method with no access to the model instance** (`State::__construct` calls `static::config()` to build the `StateConfig`). So the transition graph is fixed for the whole `FulfilmentState` hierarchy and **cannot vary by a `method` column**. Per-method graphs are reconciled in three moves:

- **Spatie sees the union — and must.** `DefaultFulfilmentStateConfig` stops hardcoding the five states and asks the manifest for the **union** of every registered method's `states()` and `transitions()`. This isn't only for breadth: Spatie auto-discovers state classes by scanning the base class's *own directory* (`resolveStateMapping()` over `States/Fulfilment/`), so a consumer's `PrescriptionVerified` in their namespace is invisible unless registered. The manifest union is the only thing that makes consumer states castable at all.

- **A method-aware guard transition keeps the union honest.** `StateConfig::isTransitionAllowed()` is a pure structural key-check, so the union alone would let a `collection` parcel transition to `Shipped` (the `pending→shipped` key exists, contributed by the shipping method). To stop that without a per-row config, every union transition is registered with one guard `Transition` whose `canTransition()` consults the parcel's *own* method graph — `State::transition()` invokes it (with the model injected) before applying the change:

  ```php
  class MethodAwareTransition extends DefaultTransition   // inherits handle(): set field + save
  {
      public function canTransition(): bool
      {
          $from = $this->model->state;   // still the old state at guard time
          return in_array(
              $this->newState::class,
              $this->model->method()->transitions()[$from::class] ?? [],
          );
      }
  }
  ```

  Because a union key like `pending→shipped` is global (one transition class per key), a single generic guard reading the model's method is exactly what's needed — it handles overlapping keys completely. With it, `transitionTo()`, `canTransitionTo()` **and** `transitionableStates()` / `transitionableStateInstances()` all enforce the per-method graph *inside* Spatie. The 0022 guarantee — every state change routes through `FulfilmentState`, so the graph is enforced — holds for direct callers too: no action-layer re-check, and the admin's existing "Update status" menu becomes method-correct for free.

- **`CreateFulfilment` stamps the initial state.** Spatie allows only one default per field (`defaultStateClass`, applied on `creating`), so per-method initial states can't ride the cast default; `CreateFulfilment` stamps `$method->defaultState()` explicitly (the global default / DB `default('pending')` is just the fallback for the unblessed path).

`FulfilmentStateConfig` keeps its contract (`fulfilmentStates()`, `fulfilmentTransitions()`, `defaultFulfilmentState()`, `notificationsFor()`); the default implementation derives the first three from the manifest, registering every transition with `MethodAwareTransition`. Two boot-time constraints: all methods must be registered before the first fulfilment state is cast (Spatie process-caches the state-name → class *mapping* — the transition `StateConfig` itself is not cached but rebuilt per state construction), and the manifest should **memoize** the assembled union so that rebuild stays cheap. The seam for reshaping a *single* flow becomes "register/replace a `FulfilmentMethod`"; replacing `FulfilmentStateConfig` wholesale still swaps the entire catalogue.

### D. Line → method assignment

Fulfilment needs two stored facts about a line, both snapshots (the [[0030-fulfillable-order-lines]] reasoning — truthful at order time, indexable for the rollup):

| Column (on `order_lines`) | From | Means |
|---|---|---|
| `requires_shipping` (0030) | `Purchasable::isShippable()` | physical — routes to a physical method |
| `requires_fulfilment` (new) | `Purchasable::requiresFulfilment()` | needs a parcel at all; counts in the rollup |

`Purchasable` gains `requiresFulfilment(): bool`, **defaulting to `isShippable()`** — so every existing purchasable behaves exactly as today (physical → needs fulfilment; non-physical → none). A digital good that needs provisioning overrides it to `true`. The invariant is `requires_shipping ⟹ requires_fulfilment`.

- `Order::fulfillableLines()` **re-keys from `requires_shipping` to `requires_fulfilment`** — the natural next step after 0030 moved it off the `type` string. Physical *and* provisionable-digital lines now count; the rollup denominator is finally complete. `requires_shipping` becomes purely the physical/routing signal.
- **Assignment** runs at order creation. `EnsureInitialFulfilment` walks registered methods in `priority()` order, handing each the still-unclaimed `fulfillableLines()`; each `claim()`s a subset; a method that claims ≥1 line gets one initial fulfilment (its `defaultState()`) covering them at full quantity. One method, all-physical order → exactly today's single parcel. A basket of a delivered good + a licence key + a prescription item → three parcels (`shipping`, `digital`, `prescription`).

Core claim rules (priority ascending; lower claims first):

| Method | priority | claims |
|---|---|---|
| `digital` | 10 | lines where `requires_fulfilment && ! requires_shipping` |
| `collection` | 20 | lines where `requires_shipping` **and** the order's chosen shipping option has `collect === true` |
| `shipping` | 30 (catch-all) | remaining lines where `requires_shipping` |

The collection method reads the order's selected shipping option's `collect` flag, persisted onto the shipping order-line at creation (`CreateShippingLine` stamps `collect` into the line snapshot). This composes with the planned "stop storing shipping options as polymorphic purchasables" work (TODO) — both resolve the option from the line's snapshot/identifier rather than a fake morph.

### E. The three core methods

| | `shipping` | `collection` | `digital` |
|---|---|---|---|
| Flow | `Pending → InProgress → Shipped` (+ `Returned`, `Cancelled`) — unchanged from 0022 | `Pending → ReadyForCollection → Collected` (+ `Returned`, `Cancelled`) | `Pending → Provisioned` (+ `Cancelled`) |
| `fulfilledState()` | `Shipped` | `Collected` | `Provisioned` |
| `usesTracking()` | `true` | `false` | `false` |
| `location` | ships-from | collect-from (the counter) | not meaningful (kept, defaults to default location) |
| terminal verb | `ship($tracking)` | `fulfil()` → "Mark collected" | `fulfil()` → "Mark fulfilled" |

New state classes under `States/Fulfilment/`: `ReadyForCollection` (`ready-for-collection`, `Outstanding`), `Collected` (`collected`, `Fulfilled`), `Provisioned` (`provisioned`, `Fulfilled`). The existing five are unchanged.

`shipping` is the regression-sensitive piece: its flow, states, tracking and `shipped_at` behaviour are exactly 0022/0024, now expressed as a registered method. Most of the implementation work and test churn is moving today's behaviour onto the generic path **without changing it**, not the new methods.

Returns fall out for free: a method that omits the `→ Returned` transition simply can't be returned — no special-casing. `collection` allows it (a customer brings goods back); `digital` doesn't.

### F. Rollup

`ResolveFulfilmentStatus` changes only in how it names the dispatched/returned states — from the literals `['shipped', 'returned']` to the manifest's category lookup — and in its denominator (`fulfillableLines()`, now `requires_fulfilment`). The structure is identical:

```php
$dispatched = FulfilmentMethods::stateNamesIn(FulfilmentStateCategory::Fulfilled)
    + FulfilmentMethods::stateNamesIn(FulfilmentStateCategory::Returned);
$returned   = FulfilmentMethods::stateNamesIn(FulfilmentStateCategory::Returned);
// …whereIn('state', $dispatched) / whereIn('state', $returned), unchanged math
```

A mixed order is fulfilled only when its shipping parcel **and** its digital parcel are both done — the digital-only "instantly fulfilled" bug is gone for provisionable goods, while truly-auto lines (`requires_fulfilment = false`: services, donations) stay out of the denominator and still resolve `Fulfilled`.

### G. Verbs & actions

Model verbs ([[0029-entry-point-conventions]]) stay thin and are gated by method:

- `Fulfilment::ship(array $tracking = [])` — unchanged; the tracking-bearing specialisation (`shipping`). Throws if the method doesn't `usesTracking()`.
- `Fulfilment::fulfil()` — **new** generic verb: advance to the method's `fulfilledState()` with no tracking (collection → `Collected`, digital → `Provisioned`, custom → its terminal). The admin labels it per method ("Mark collected" / "Mark fulfilled").
- Intermediate steps (`ReadyForCollection`, a consumer's `prescription-verified`) use the existing `transition($state)` verb.
- `cancel()`, `markReturned()`, `split()`, `merge()`, `moveLinesTo()`, `hold()`, `release()`, `changeLocation()`, `addTracking()`, `transition()` are unchanged, but the ones that reason about lifecycle position now test **category** rather than literal names (e.g. split/merge require `Outstanding`; `markReturned()` requires `Fulfilled`). Stamping/clearing `shipped_at` moves into `TransitionFulfilment` keyed on the target's category (§B).

`CreateFulfilment` gains an optional `method` in its attributes (default `shipping`) and stamps the method's `defaultState()`. **`MergeFulfilments` / `MoveFulfilmentLines` gain a same-`method` guard** alongside the existing same-order / same-location guards — you can't fold a shipping parcel into a collection one.

### H. Admin (Filament)

The fulfilments panel is the `OrderFulfilments` Livewire component plus its card-list blade (0022). Both are currently shipping-hardcoded — the state badge colour/icon, the "Update status" routing map, and the `canAddTracking` / `canCancel` gates all branch on the literal `'shipped'`. Making it method-aware is mostly **replacing those literals with category/method lookups**, plus one new action and a per-method extension seam. The order screen is otherwise unchanged, and everything still routes through the core verbs (§G) so an API/agent consumer and the admin share one path.

- **Card chrome.** Each parcel card gains a **method badge** (`$fulfilment->method()->getLabel()`). The state badge's colour and icon derive from the state's `FulfilmentStateCategory` (a four-entry map) instead of the per-state-name maps, so a consumer's custom states render sensibly with no blade change. The "Shipped at …" line becomes a category-driven handed-over label (collected at / fulfilled at) off `shipped_at` (§B). An order may now show several cards of different methods (a `shipping` and a `digital` parcel) — no layout change; the list already renders N parcels.
- **New `fulfil` action.** A no-tracking terminal action for methods where `usesTracking() === false`, labelled and iconed from the method ("Mark collected" / "Mark fulfilled"), delegating to `$fulfilment->fulfil()`. The existing `ship` action — and `trackingFields()` / `addTracking` / `removeTracking` — are gated on `$fulfilment->method()->usesTracking()`, so collection/digital cards show no carrier/tracking UI.
- **Method-correct "Update status" menu.** `statusTransitions()` keeps building from `transitionableStateInstances()` (guard-filtered to the parcel's method, §C); only its hardcoded exclusions generalise. Drop the method's `defaultState()` — reverting to it is the destructive "Cancel fulfilment" ⋮ action (un-progress, back to the unfulfilled pool), not a forward menu step (was the literal `'pending'`) — and any `Cancelled`-category target, which stays programmatic-only and unsurfaced as in 0022 (was `'cancelled'`); while the parcel is on hold, hide `Fulfilled`-category targets (was the `'shipped'`-while-held rule). Each remaining target routes to its action: the method's `fulfilledState()` → `ship` when `usesTracking()` else `fulfil`; a `Returned`-category target → `return`; otherwise the generic confirmation `transition`.
- **Per-method capability gates.** The card's `@php` flags become category/method-driven: `canAddTracking` = `usesTracking()` and a `Fulfilled`-category state; `canCancel` = the parcel has moved off its `defaultState()` and isn't terminal; split/merge require the `Outstanding` category (§G), and the merge-candidate list (`mergeTargets()`) gains the same-`method` filter to mirror the core guard.
- **Consumer-method action seam.** Core renders the default action set above for every method; a consumer shapes it through the existing extension mechanism — `LunarPanel::extensions()` and the component's `CallsHooks` trait, already used by `extendFulfilmentLineDetails` — via a new `extendFulfilmentActions(array $actions, Fulfilment $fulfilment): array` hook: add a bespoke action (e.g. a "Verify prescription" form), remove one that doesn't apply (digital drops change-location), or relabel. Keeping this hook in the admin package — rather than a `filamentActions()` method on the core `FulfilmentMethod` contract — keeps Filament out of core (config-for-data, container-for-behaviour, [[0016-service-layer-di]]). **Agent-native parity** (any action a user can take, an agent can take): a custom action only *collects input* and routes through a verb; any state or data change — including writing per-method data to `Fulfilment::$meta` or a consumer column — goes through the same verb the agent/API path uses, never buried in the Filament closure.

### I. Config

`config/fulfilment.php` gains a documented `methods` key, mirroring `shipping.carriers` — data-shaped methods (key, label, flow, claim rule) can be declared in config via a `GenericFulfilmentMethod`; methods needing real logic implement `FulfilmentMethod` and register in a service provider (container-for-behaviour, config-for-data — [[0016-service-layer-di]]).

## Alternatives considered

- **A closed `method` enum on `Fulfilment`** (`shipping | collection | digital`). Rejected: it closes the one extension point core otherwise leaves open, and a pharmacy/slot-booking/3PL flow would have to fork core or abuse `meta`. The registry costs little more (it mirrors `CarrierManifest`) and makes the bespoke case a registration.
- **Per-row Spatie state graphs.** Rejected as infeasible without fighting Spatie (static per-class config). The union-registration + method-as-authority split (§C) gets per-method graphs with stock Spatie.
- **Free-form per-method graphs with no fixed categories.** Rejected: the order rollup (and split/merge/return) would have no method-agnostic way to ask "is this parcel done?". Categories (§B) are the minimal fixed vocabulary that keeps the rollup correct while the states stay open.
- **No new line column — derive the rollup denominator from live `claim()` results.** Rejected: re-running method claims on every recompute couples the rollup to mutable method config and reintroduces the runtime derivation 0030 rejected; a stored, indexed `requires_fulfilment` snapshot is consistent with `payment_status` / `fulfilment_status` / `requires_shipping`.
- **Collection as a pure `ShippingOption` flag, no fulfilment change.** Rejected: it throws away exactly what a trade counter needs — a "ready for collection" notification and a "collected" record. Collection earns a flow.
- **Reuse one graph and only relabel the terminal per method.** Rejected: the medication case needs *new* states (verification), not relabelled ones — relabelling can't represent an extra step.

## Migration impact

- **Database (baseline migrations edited in place — v2 pre-release, same rule as 0017–0030):**
  - `..._create_order_lines_table.php` — add `requires_fulfilment` (boolean, default `false`, indexed, after `requires_shipping`).
  - `..._create_fulfilments_table.php` — add `method` (string, indexed, default `shipping`).
  - No new tables.
- **No core data migration** (v2 has no live data). `OrderLineFactory` sets `requires_fulfilment` defaulting to its `requires_shipping` value so existing tests are unaffected.
- **Breaking changes to the public contract surface:**
  - `Contracts\Purchasable` gains `requiresFulfilment(): bool`. Additive with a sensible default on the shipped purchasable base (returns `isShippable()`); consumers implementing `Purchasable` from scratch must add it. Rector note in `LunarSetList`; covered in the upgrade guide (same handling as 0030's `fulfillableLines()`).
  - `States\Fulfilment\FulfilmentState` gains abstract `category()`. Core states implement it; a consumer's custom fulfilment states must declare a category.
  - `Order::fulfillableLines()` re-keyed from `requires_shipping` to `requires_fulfilment` — superset, so physical orders are unaffected; provisionable-digital orders are newly counted (the bug fix). Behavioural.
  - `Fulfilment` gains `method` + `method()`; `CreateFulfilment` accepts `method`; `MergeFulfilments`/`MoveFulfilmentLines` add a same-method guard.
  - `DefaultFulfilmentStateConfig` now derives its catalogue from the `FulfilmentMethodManifest`. Consumers who replaced `FulfilmentStateConfig` wholesale (rather than registering a method) must adapt; documented.
  - New surface (all additive): `Contracts\FulfilmentMethod`, `Contracts\FulfilmentMethodManifest`, `Manifests\FulfilmentMethodManifest`, `Facades\FulfilmentMethods`, `Drivers\FulfilmentMethods\{Shipping,Collection,Digital}` + a `GenericFulfilmentMethod`, `Enums\FulfilmentStateCategory`, `States\Fulfilment\{ReadyForCollection,Collected,Provisioned}`, `States\Fulfilment\MethodAwareTransition`, `Fulfilment::fulfil()`, `config('lunar.fulfilment.methods')`.
- **Upgrade path (stage 3, `packages/upgrade`):** backfill `order_lines.requires_fulfilment = (type = 'physical')` for v1 orders (v1 had no other signal — mirrors 0030's `requires_shipping` backfill); historical fulfilments don't exist in v1, so `method` defaults to `shipping` and no `Fulfilment` rows are created (per 0022's upgrade choice). One-way ([[feedback-upgrade-migrations-no-down]]).
- **Translation / locale impact (16 locales):** English first, then mirrored. New per-parcel state labels `states.fulfilment.{ready-for-collection,collected,provisioned}`; method labels `fulfilment.methods.{shipping,collection,digital}`; admin labels for the `fulfil()` action per method + the method badge; exception keys `exceptions.fulfilment_method_mismatch` (cross-method merge/move) and `exceptions.fulfilment_method_no_tracking` (`ship()` on a non-tracking method).
- **Filament / admin impact** — §H: method badge + category-driven state badge per card, a new `fulfil` action for non-tracking methods, tracking UI gated on `usesTracking()`, the category/method-generalised "Update status" menu, the same-`method` merge-candidate filter, and the `extendFulfilmentActions` seam for consumer methods. The order screen is otherwise unchanged. Verify end-to-end against `https://lunar-v2.test` (Herd).

## Open questions

- **Per-method "detail" beyond tracking.** `usesTracking(): bool` covers the three core methods; a richer per-method detail schema (e.g. a prescription reference, a delivery-slot datetime) is deferred — a method can use `Fulfilment::$meta` until a real case justifies a typed seam.
- **Choosing the collection location at checkout.** Picking *which* counter to collect from is a storefront concern; `Location` already supports many, so this spec leaves the selection to a later storefront spec and defaults to the order's resolved location.
- **Default customer notifications per method** (ready-for-collection, collected, provisioned) tie into the "ship default professional notifications" TODO item — keys are reserved here; the classes/templates land there.

## References

- [[0022-order-fulfilments]] — the `Fulfilment` model, lifecycle, `EnsureInitialFulfilment`, `ResolveFulfilmentStatus` and `FulfilmentStateConfig` this generalises.
- [[0030-fulfillable-order-lines]] — `requires_shipping` / `fulfillableLines()`; this extends the same reasoning to `requires_fulfilment`.
- [[0024-shipping-carriers]] — `CarrierManifest` / `Carriers`, the registry pattern `FulfilmentMethodManifest` mirrors, and the tracking that only `usesTracking()` methods carry.
- [[0029-entry-point-conventions]] — model verbs delegating to action contracts (`ship()`, the new `fulfil()`).
- [[0016-service-layer-di]] — container-for-behaviour / config-for-data; manifests bound in `registerServices()`.
- `Drivers/ShippingMethods/Collection.php` (table-rate-shipping) — the existing `collect: true` shipping option the `collection` method consumes.
