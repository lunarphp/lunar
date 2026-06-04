# 0021 — State machines (and retiring soft-deletes)

- Status: completed
- Author: Glenn Jacobs
- Created: 2026-05-28
- Revised: 2026-06-01
- TODO item: "Implement state machines, replacing soft-deletes — products (draft, published, archived) & orders (payment, fulfilment and order status)"

> **2026-06-01 revision.** §D–§K originally split the order into three coordinated machines (`payment_status` × `fulfilment_status` → a computed `order_status`). That decomposition was premature: `payment_status` is a rollup of `transactions` (which exist today) and `fulfilment_status` is a rollup of *order fulfilments* (a concept not yet built). Designing those rollups — and the combined headline derived from them — half here and half in the fulfilments spec is churn for no gain. So this PR walks the order work **back to a single status**, now done properly as the typed, transition-guarded `OrderState` machine that the rest of the spec is about. The payment/fulfilment decomposition, the derived combined status, and the per-fulfilment lifecycle all move to the **next spec** ("Order fulfilments & order status"), which can design them once with every source record on the table. `payment_status` / `fulfilment_status` columns, the `States/Order/{Payment,Fulfilment}` trees, `OrderStateConfig`'s resolver, `OrderStateCategory`, `Enums\OrderStatus`, and `computeOrderStatus()` are **dropped from this PR**. The PR is pre-merge, so the spec is edited in place rather than superseded; the already-committed three-machine code is reverted to match (see Migration impact).

## Problem

Lifecycle state across the package is spread between three uncoordinated mechanisms, none of which captures the actual rules:

- **Free-form `status` strings.** `products.status` and `orders.status` are plain indexed strings (`2026_01_01_000031_create_products_table.php:15`, `2026_01_01_000028_create_orders_table.php:15`). Any value is accepted; there is no list of allowed states, no notion of which transitions are legal, and no place to hang per-state behaviour. `Order::statusLabel()` reaches into `config('lunar.orders.statuses')` (`Models/Order.php:93`) to translate the string back to a label.
- **A single boolean masquerading as state.** `Channel` casts an `enabled` column to bool (`Models/Channel.php:35`) even though no `enabled` column ships in the v2 channel migration — leftover from v1's enabled/disabled split. Collections inherit the same handle-keyed `enabled` flag idea on the customer-group pivot (`Models/Product.php:178-184`). Neither captures "draft vs published vs archived".
- **Soft-deletes used as a hidden lifecycle state.** `Product`, `ProductVariant`, `Channel`, `Cart`, and `Staff` use `SoftDeletes`. For products and channels in particular this is functioning as "archived" — rows are tombstoned, hidden from the storefront, but kept around for orders and reporting. That conflates two things (an admin lifecycle and a real deletion) and forces every query to remember `withTrashed()` semantics.
- **No transition events.** Nothing in core dispatches an event when an order's status changes, so notifications, webhook fan-out, and downstream cache invalidation each have to roll their own listener wired off Eloquent `updated`.

Net effect: states are stringly-typed, illegal transitions cannot be prevented, and "archived" is smuggled in via soft-deletes.

(Real-world orders also track payment and fulfilment progress as functions of underlying records — `transactions` today, order fulfilments soon. Deriving the order status from those is the **order-fulfilments spec**'s job, not this one; see the Proposal and the alternatives. This spec gets the single order status properly typed.)

## Proposal

Adopt `spatie/laravel-model-states` v2 for every lifecycle column in core. Replace soft-deletes used as archive flags with explicit `Archived` states. For orders, replace the free-form `status` string with a single typed, transition-guarded `OrderState` machine — the order lifecycle the merchant manages (`AwaitingPayment → InProcess → Shipped → Complete`, plus `OnHold` / `Cancelled` / `Refunded` / …). Decomposing that lifecycle into payment- and fulfilment-derived rollups is deferred to the next spec, where the order-fulfilment concept lands and the rollups have real source records. All state classes live under `Lunar\Core\States\…`.

### A. Dependency

Add `spatie/laravel-model-states: ^2` to `packages/core/composer.json`. Pinned to v2 because v1 lacks the typed `transitionableStates()` config that this spec relies on.

### B. Directory layout under `packages/core/src/`

```
States/
├── Channel/
│   ├── ChannelState.php
│   ├── Active.php
│   └── Inactive.php
├── Product/
│   ├── ProductState.php
│   ├── Draft.php
│   ├── Published.php
│   └── Archived.php
├── Collection/
│   ├── CollectionState.php
│   ├── Draft.php
│   ├── Published.php
│   └── Archived.php
└── Order/
    ├── OrderState.php                 ← abstract base (single order lifecycle machine)
    ├── OrderStateConfig.php           ← contract, moves to Contracts/ (see §E)
    ├── DefaultOrderStateConfig.php
    └── Order/                         ← concrete OrderState classes
        ├── AwaitingPayment.php
        ├── PaymentFailed.php
        ├── Backordered.php
        ├── InProcess.php
        ├── PartiallyShipped.php
        ├── Shipped.php
        ├── Complete.php
        ├── Returned.php
        ├── Refunded.php
        ├── OnHold.php
        └── Cancelled.php
```

> **Next spec.** The order-fulfilments spec re-introduces `States/Order/Payment/*` and `States/Order/Fulfilment/*` (and `Enums/OrderStateCategory`), this time as rollups of `transactions` and `Fulfilment` records rather than columns the merchant sets, and converts this single `OrderState` into a status derived from them. Keep the `OrderState` concrete classes free of any `Order`-model coupling so the fulfilment-lifecycle classes can reuse the same base.

Per spec 0013, contracts (`OrderStateConfig`) move to `Contracts/Orders/OrderStateConfig.php`; concrete config lives next to the states it composes (`States/Order/DefaultOrderStateConfig.php`). `States/` is a new top-level folder — spec 0013's folder list is extended to include it ("Spatie model-states classes; one subfolder per machine").

### C. Simple binary-ish machines

#### Channel — `Active ⇄ Inactive`

Default: `Active`. Transitions: `Active → Inactive`, `Inactive → Active`.

Replaces the dead `enabled` bool cast (`Models/Channel.php:35`) and the `SoftDeletes` trait. Setting a channel to `Inactive` is what "disabled" meant in v1.

#### Product — `Draft → Published ↔ Archived → Draft`

Default: `Draft`. Transitions:

- `Draft → Published`
- `Published → Archived`
- `Published → Draft`
- `Archived → Draft`

(The prototype write-up named these `Draft / Active / Discontinued`; this spec renames to `Draft / Published / Archived` because "published" is the verb the admin already uses — see the existing `BulkPublish` / `BulkUnpublish` actions from spec 0009 — and "archived" matches the soft-delete behaviour it replaces. `$name` values: `draft`, `published`, `archived`.)

#### Collection — same shape as Product

Default: `Draft`. Same transitions. `$name`: `draft`, `published`, `archived`.

#### Storefront filtering

The storefront should treat `Published` as the only publicly visible state. Add a `whereVisible()` query scope on `Product` and `Collection` (concrete on `Models\Base`'s subclasses) that compiles to `where('status', Published::$name)` so callers do not have to import state classes for the common case. Existing callsites that filter on `withoutTrashed()` migrate to this scope.

#### Cast wiring

```php
// Models/Product.php
protected function casts(): array
{
    return [
        // ...
        'status' => ProductState::class,
    ];
}
```

PHPDoc: `@property ProductState $status` so PHPStan resolves `transitionTo()` correctly.

### D. Order — a single lifecycle machine

The order keeps **one** status column, replacing the v1 free-form string with a typed, transition-guarded `OrderState`. The merchant drives it by hand (or actions do, on payment/dispatch). Decomposition into payment/fulfilment rollups is the next spec's job.

#### Column

Baseline migration `2026_01_01_000028_create_orders_table.php` is edited in place (v2 is pre-release; same rule applied by specs 0017, 0018, 0019):

- Keep the existing `string('status')->index()`; set its default to `awaiting-payment`. No `payment_status` / `fulfilment_status` / `order_status` columns are added in this PR.

Factories under `packages/core/src/Database/Factories/OrderFactory.php` emit `status` and gain states for the common lifecycle points (`awaitingPayment()`, `inProcess()`, `shipped()`, `complete()`, `cancelled()`).

#### `OrderState` (abstract)

```php
abstract public function label(): string;
```

The single order lifecycle. `$name` values double as the stored column value and the translation-key suffix.

| State              | `$name`             |
|--------------------|---------------------|
| `AwaitingPayment`  | `awaiting-payment`  |
| `PaymentFailed`    | `payment-failed`    |
| `Backordered`      | `backordered`       |
| `InProcess`        | `in-process`        |
| `PartiallyShipped` | `partially-shipped` |
| `Shipped`          | `shipped`           |
| `Complete`         | `complete`          |
| `Returned`         | `returned`          |
| `Refunded`         | `refunded`          |
| `OnHold`           | `on-hold`           |
| `Cancelled`        | `cancelled`         |

Default: `AwaitingPayment`. Transitions are declared in `OrderStateConfig::orderTransitions()` (§E) so consumers can reshape the lifecycle without touching core. The shipped default graph:

- `AwaitingPayment → InProcess, PaymentFailed, Backordered, OnHold, Cancelled`
- `PaymentFailed → AwaitingPayment, Cancelled`
- `Backordered → InProcess, OnHold, Cancelled`
- `InProcess → PartiallyShipped, Shipped, OnHold, Cancelled`
- `PartiallyShipped → Shipped, Returned, Cancelled`
- `Shipped → Complete, Returned`
- `Complete → Returned, Refunded`
- `Returned → Refunded`
- `OnHold → AwaitingPayment, InProcess, Cancelled` (resume)
- `Cancelled → Refunded`
- `Refunded` — terminal

`PartiallyShipped` / `Backordered` are settable by hand here; the next spec makes them *derived* outcomes of the fulfilment rollup (a single fulfilment can't be "partially shipped"). PHPDoc on the base: `@property OrderState $status`.

### E. `Contracts\OrderStateConfig`

```php
namespace Lunar\Core\Contracts;

use Lunar\Core\States\Order\OrderState;

interface OrderStateConfig
{
    /** @return array<class-string<OrderState>> */
    public function orderStates(): array;

    /** @return array<class-string<OrderState>, list<class-string<OrderState>>> */
    public function orderTransitions(): array;

    /** @return class-string<OrderState> */
    public function defaultOrderState(): string;

    /** @return list<class-string<\Illuminate\Notifications\Notification>> */
    public function notificationsFor(OrderState $state): array;
}
```

The abstract `OrderState` base reads the bound `OrderStateConfig` to register its states and transitions. This is the **single seam** for reshaping the order lifecycle: a downstream consumer extends `DefaultOrderStateConfig`, adds states + transitions, and binds the subclass in their service provider — the machine picks them up without any core change.

```php
class MyOrderStateConfig extends DefaultOrderStateConfig
{
    public function orderStates(): array
    {
        return [...parent::orderStates(), AwaitingStock::class];
    }

    public function orderTransitions(): array
    {
        return [
            ...parent::orderTransitions(),
            AwaitingPayment::class => [AwaitingStock::class, ...parent::orderTransitions()[AwaitingPayment::class]],
            AwaitingStock::class   => [InProcess::class, Cancelled::class],
        ];
    }
}
```

Bind in a service provider's `register()` (not `boot()`) so the catalogue is in place before any model uses the state cast. Spatie's `State` base caches the resolved state mapping per class for the lifetime of the process — under Laravel Octane this cache survives between requests, so runtime rebinding is not supported.

Bind in `LunarServiceProvider::registerServices()` (the spec 0016 home for non-action service bindings):

```php
$this->app->bind(OrderStateConfig::class, DefaultOrderStateConfig::class);
```

`OrderStateConfig` is **not** an action — it stays out of `ActionServiceProvider::$actions`. Consumers swap the binding in their own provider to reshape the lifecycle.

### F. Order — cast and label

The status is a plain cast column; there is no resolver, no recompute, nothing to keep in sync.

```php
// Models/Order.php
protected function casts(): array
{
    return [
        // ...
        'status' => OrderState::class,
    ];
}
```

PHPDoc: `@property OrderState $status`. `Order::statusLabel()` becomes a thin pass-through to `$this->status->label()`, kept for backwards compatibility.

### G. `OrderObserver` — rewrite

Replaces the existing `Observers/OrderObserver.php` (which only logs status-change activity today).

A single status column means no resolver, no `saveQuietly()`/`forceFill()`, no recursion guard. The observer logs the change and dispatches the event.

```php
public function updating(Order $order): void
{
    if ($order->isDirty('status')) {
        activity()
            ->causedBy(auth()->user())
            ->performedOn($order)
            ->event('status-update')
            ->withProperties([
                'new'      => $order->status->getValue(),
                'previous' => $order->getOriginal('status')->getValue(),
            ])
            ->log('status-update');
    }
}

public function updated(Order $order): void
{
    if ($order->wasChanged('status')) {
        OrderStatusUpdated::dispatch(
            $order,
            $order->getOriginal('status'),
            $order->status,
        );
    }
}
```

Registration stays where it is — `LunarServiceProvider::bootingPackage()` already does `Order::observe(OrderObserver::class)`.

### H. `Events\OrderStatusUpdated`

```php
namespace Lunar\Core\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Lunar\Core\Models\Order;
use Lunar\Core\States\Order\OrderState;

class OrderStatusUpdated
{
    use Dispatchable;

    public function __construct(
        public Order $order,
        public OrderState $previousStatus,
        public OrderState $newStatus,
    ) {}
}
```

It fires whenever the order's `status` changes — the single signal notifications and webhooks key off.

### I. `Listeners\SendOrderStatusNotifications`

```php
public function __construct(
    protected OrderStateConfig $stateConfig,
) {}

public function handle(OrderStatusUpdated $event): void
{
    foreach ($this->stateConfig->notificationsFor($event->newStatus) as $class) {
        $event->order->notify(new $class($event->order));
    }
}
```

The listener resolves notifications through `OrderStateConfig::notificationsFor()` rather than reading `config()` directly — this keeps the binding the single seam for "everything about order states" and lets consumers move notification resolution out of config entirely by overriding the method (e.g. fetching from the database or from a feature-flagged map).

`DefaultOrderStateConfig::notificationsFor()` reads `lunar.orders.notifications.{$state::$name}` so the common case stays a flat config key:

```php
// config/orders.php
'notifications' => [
    'shipped' => [App\Notifications\OrderShipped::class],
    'complete' => [App\Notifications\OrderComplete::class],
],
```

`Order` uses `Illuminate\Notifications\Notifiable` so `$order->notify(...)` works directly. Wired in `LunarServiceProvider::bootingPackage()`:

```php
Event::listen(OrderStatusUpdated::class, SendOrderStatusNotifications::class);
```

### J. Retiring soft-deletes

Soft-deletes were doing two jobs: keeping orphan references intact (a real concern) and hiding archived records (now the `Archived` state's job). They are unpicked per model:

| Model            | Today          | After                                                                                                                                              |
|------------------|----------------|----------------------------------------------------------------------------------------------------------------------------------------------------|
| `Product`        | `SoftDeletes`  | Drop the trait, drop `deleted_at`/`deleted_at` index from the baseline. "Archived" handled by `Archived` state. Order lines hold a snapshot (existing `purchasable_*` denormalisation) so the FK becoming `nullOnDelete` is safe. |
| `ProductVariant` | `SoftDeletes`  | Same — drop the trait, rely on `Archived` on the parent product plus order-line snapshot.                                                          |
| `Collection`     | `SoftDeletes`  | Drop the trait, drop `deleted_at`. `Archived` state replaces it.                                                                                   |
| `Channel`        | `SoftDeletes`  | Drop the trait, drop `deleted_at`. `Inactive` state replaces it.                                                                                   |
| `Cart`           | `SoftDeletes`  | **Keep.** Cart soft-delete is not lifecycle state — it lets order-tied carts hang around for replay/audit. Out of scope for this spec.             |
| `Staff`          | `SoftDeletes`  | **Keep.** Auth concern, not commerce lifecycle. Out of scope.                                                                                      |

Per the v2 baseline rule (pre-release), the relevant baseline migrations are edited in place to drop `softDeletes()` and the `deleted_at` index. Foreign keys that previously relied on tombstoned rows (`order_lines.purchasable_*`) are reviewed: where the lines already snapshot the data they need (`purchasable_type`, `description`, etc. on `OrderLine`), the morph can become unconstrained and the snapshot is authoritative. Any FK that does **not** have a snapshot stays as-is and we document the constraint.

`Models\Concerns\HasMacros`, `LogsActivity`, etc. are untouched.

### K. Public exposure of the state catalogue

`OrderStateConfig::orderStates()` gives the admin and the storefront API a typed list of available order states. The Filament admin uses it to populate the status select input in place of the hardcoded `config('lunar.orders.statuses')` array, gated by `transitionableStates()` so the UI only offers legal next states. Filtering is a plain single-column `where('status', …)` — no derived-status scope is needed in this PR.

## Alternatives considered

- **Decompose the order status into `payment_status` + `fulfilment_status` machines in this PR** (the original §D–§F design). Deferred, not rejected outright. `payment_status` is a rollup of `transactions` and `fulfilment_status` a rollup of order fulfilments; modelling them as settable machines now — when fulfilments don't exist and the rollup rules can't be designed against real records — and then converting them to derived rollups next spec is decompose-then-recompose churn. The whole order-status story (per-fulfilment lifecycle, `payment_status` derived from transactions à la Shopify's `financial_status`, the combined headline) is told once in the fulfilments spec instead. For this PR a single typed lifecycle is enough.
- **Derive a combined status on read this PR** (resolver over payment × fulfilment). Same deferral: there is nothing to derive *from* until the payment/fulfilment rollups exist, which is next spec's scope.
- **Keep string statuses, add validation rules.** Rejected: validation gates the write but does nothing for transition enforcement. Spatie's model-states covers it and is in heavy use across the wider Laravel ecosystem.
- **Roll a bespoke state library.** Rejected: model-states v2 is small, well-typed, supports `saveQuietly()`/`forceFill()` cleanly, integrates with Eloquent casts, and is supported. Reinventing it is pure cost.
- **Keep soft-deletes, add a parallel `archived_at`.** Rejected: that gives you two ways to hide a record and forces every query to remember both. The point of the redesign is one explicit lifecycle column per model.
- **Leave the order `status` as a free-form string for now** (do only Product/Collection/Channel machines in this PR). Rejected: orders deserve the same typed, transition-guarded treatment as everything else, the concrete state classes already exist, and "single settable status now → derived status next spec" is a clean evolution rather than a decompose. Leaving the string would just defer the typing work twice.
- **Drop the `Cancelled` / `OnHold` states and use separate `cancelled_at` / `held_at` columns.** Rejected: it splits the lifecycle across nullable timestamp columns and means the admin has to consult several fields to know what an order is doing. A single `status` machine keeps the lifecycle in one place.

## Migration impact

- **Baseline migrations edited in place.** Per the spec 0019 precedent, v2 is unreleased and the baseline is still being shaped, so the relevant `2026_01_01_*` files are edited:
  - `..._create_orders_table.php` — keep the single `status` column; set its default to `awaiting-payment`. No `payment_status` / `fulfilment_status` / `order_status` columns. **Revert** the already-committed three-column change (`payment_status` / `fulfilment_status` / `order_status` → single `status`).
  - `..._create_products_table.php` — drop `softDeletes()` and the `deleted_at` index; `status` stays but now stores `ProductState::$name` values (`draft`, `published`, `archived`).
  - `..._create_collections_table.php` — drop `softDeletes()`; add a `status` string column (collections do not have one today).
  - `..._create_channels_table.php` — drop `softDeletes()`; add a `status` string column (channels do not have one today; the `enabled` cast in `Models/Channel.php:35` is removed alongside).
  - Once v2 ships, this rule flips back: subsequent state additions go in new migrations.
- **No core data migration.** v2 has no live data to convert.
- **Data migration (stage 3, `packages/upgrade`).** Add one-way migrations to:
  - Map v1 `products.status` values (`published` / `draft`) to v2 (`published` / `draft`); v1 trashed products → `archived`. Null the `deleted_at` column afterwards. ([[feedback-upgrade-migrations-no-down]])
  - Map v1 `channels.enabled` boolean → `status` (`active` / `inactive`); null `deleted_at`.
  - Collections: trashed → `archived`, otherwise `published` (collections were not lifecycle-gated in v1).
  - Map v1 `orders.status` values onto the `OrderState` `$name` set. Reasonable defaults: `awaiting-payment` → `awaiting-payment`; `payment-received` → `in-process`; `dispatched` → `shipped`; `cancelled` → `cancelled`; unknown/custom values fall back to `awaiting-payment`. The full mapping table is finalised in the upgrade package PR. (The payment/fulfilment split arrives with the order-fulfilments spec, with its own data migration off `transactions` and the new fulfilment records.)
- **Breaking changes to the public contract surface:**
  - `Models\Order::$status` stays a single column but is now cast to `OrderState` (was a free-form string). PHPDoc and the `casts()` array change; assigning an arbitrary string breaks.
  - `Models\Product`, `ProductVariant`, `Channel`, `Collection` lose `SoftDeletes`; any caller using `withTrashed()` / `onlyTrashed()` / `restore()` breaks. Rector rule: replace `->onlyTrashed()` with `->where('status', Archived::$name)` where the intent is "show archived". `->restore()` becomes `->status->transitionTo(Draft::class)` (or `Active::class` for channels).
  - `Models\Channel` loses the `enabled` boolean cast.
  - `Models\Order::statusLabel()` now delegates to `$order->status->label()`. The method is retained, so no rename is forced; callers reading the raw string get an `OrderState` instance instead (`(string) $order->status` / `$order->status->getValue()` for the name).
  - `config('lunar.orders.statuses')` is no longer the source of truth for available statuses (the state classes are); notification configuration moves to `lunar.orders.notifications.{name}` and is resolved through `OrderStateConfig::notificationsFor()`.
  - These are listed under §J of the `LunarSetList` in the `upgrade` package.
- **Upgrade path for v1.x consumers (stage 3).** Rector rules cover the renames/removals above. Data migration covers the column changes. Soft-delete removal is one-way — restoring trashed rows must happen *before* the v2 upgrade runs.
- **Translation / locale impact.** Each state's `label()` returns a translation key (e.g. `lunar::states.product.published`). New keys land in all 16 locales under each package's `resources/lang/` — English first, mirrored placeholders for the other 15. Affected keys:
  - `states.product.{draft,published,archived}`
  - `states.collection.{draft,published,archived}`
  - `states.channel.{active,inactive}`
  - `states.order.{awaiting-payment,payment-failed,backordered,in-process,partially-shipped,shipped,complete,returned,refunded,on-hold,cancelled}` (`OrderState` labels; payment/fulfilment keys arrive with the order-fulfilments spec)
- **Filament / admin impact.** The order resource keeps its single status badge and select, now typed:
  - The status select is driven by `OrderStateConfig::orderStates()`, gated by `transitionableStates()` so the UI only offers legal next states; the badge renders `$order->status->label()`.
  - "Place on hold" / "Cancel" actions transition `status` to `OnHold` / `Cancelled`; a "Resume" action transitions back out.
  - The order-list "Status" filter is a plain `where('status', …)`.
  - Product / collection list filters add a "Status" select (`Draft / Published / Archived`); the soft-delete filter is removed.
  - Channel switcher hides `Inactive` channels by default.
  - Verify end-to-end against the host app at `https://lunar-v2.test` (Herd) per the package convention.

## Acceptance checks

Feature and unit tests in `packages/lunar/tests/core/Unit/States/` and `tests/core/Feature/Orders/`:

- Each simple machine (Channel, Product, Collection) has one passing test per allowed transition and one failing test (`CouldNotPerformTransition`) per disallowed transition; defaults assert on factory-created models.
- `OrderStateTransitionsTest` covers the §D default transition graph (one passing test per allowed transition) plus a couple of disallowed ones (e.g. `Cancelled → AwaitingPayment` and any transition out of `Refunded` throw `CouldNotPerformTransition`); the default `AwaitingPayment` asserts on a factory-created order.
- `DefaultOrderStateConfigTest` asserts `orderStates()`, `defaultOrderState()`, and `orderTransitions()` match §D, and that a subclass adding a state + transitions is picked up by the machine.
- Integration: changing `status` on a real `Order` dispatches `OrderStatusUpdated` exactly once with the previous/new `OrderState`; an unchanged save dispatches nothing.
- Integration: a transition not in the graph throws `CouldNotPerformTransition` and leaves `status` unchanged.
- Activity log: writing a new `status` produces a `status-update` entry with `new` / `previous` properties.
- `whereVisible()` on `Product` / `Collection` returns only `Published` rows.
- `ArchitectureTest` extension: every state class extends its abstract base and exposes a static `$name`; every `States/` subfolder has an `*State` abstract.

## Open questions

- **Notification config relocation** — moving from `lunar.orders.statuses` to `lunar.sales.orders.statuses` matches where `sales.orders.*` already lives in `packages/core/config/`. Confirm during implementation that no downstream package reads the old key directly.
- **Order-fulfilments handover** — the next spec converts this single `OrderState` into a status derived from `transactions` (payment) and `Fulfilment` records (fulfilment). Confirm the `OrderState` concrete classes stay free of `Order`-model coupling so the per-fulfilment lifecycle can reuse the abstract base, and that `OrderStateConfig` is the only thing that needs reshaping when the derivation lands.
- **Order-line FK on archived products** — `order_lines.purchasable_*` already snapshots the data needed to render historical orders, but a quick audit during stage 1 should confirm there is no read path that lazily re-resolves through the morph and assumes the product still exists.
- **Cart and Staff soft-deletes** — left in for now (§J). Whether `Cart` adopts its own state machine (`Active / Abandoned / Converted`) is a follow-up, not part of this spec.

## References

- [[0013-base-directory-reorganisation]] — `States/` is a new top-level folder following the same one-concern-per-folder rule; `Contracts/Orders/OrderStateConfig` follows the no-`Interface`-suffix rule.
- [[0016-service-layer-di]] — `OrderStateConfig` is bound in `LunarServiceProvider::registerServices()`; it is the single seam for reshaping the order lifecycle.
- [[0009-filament-actions-and-global-search]] — existing `BulkPublish` / `BulkUnpublish` / `BulkArchive` actions retarget onto `ProductState` transitions.
- `spatie/laravel-model-states` v2 — https://spatie.be/docs/laravel-model-states/v2
- `packages/upgrade` — Rector rules and v1 → v2 data migrations for the column / API changes above.
