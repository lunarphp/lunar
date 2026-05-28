# 0021 — State machines (and retiring soft-deletes)

- Status: implemented
- Author: Glenn Jacobs
- Created: 2026-05-28
- TODO item: "Implement state machines, replacing soft-deletes — products (draft, published, archived) & orders (payment, fulfilment and order status)"

## Problem

Lifecycle state across the package is spread between three uncoordinated mechanisms, none of which captures the actual rules:

- **Free-form `status` strings.** `products.status` and `orders.status` are plain indexed strings (`2026_01_01_000031_create_products_table.php:15`, `2026_01_01_000028_create_orders_table.php:15`). Any value is accepted; there is no list of allowed states, no notion of which transitions are legal, and no place to hang per-state behaviour. `Order::statusLabel()` reaches into `config('lunar.orders.statuses')` (`Models/Order.php:93`) to translate the string back to a label.
- **A single boolean masquerading as state.** `Channel` casts an `enabled` column to bool (`Models/Channel.php:35`) even though no `enabled` column ships in the v2 channel migration — leftover from v1's enabled/disabled split. Collections inherit the same handle-keyed `enabled` flag idea on the customer-group pivot (`Models/Product.php:178-184`). Neither captures "draft vs published vs archived".
- **Soft-deletes used as a hidden lifecycle state.** `Product`, `ProductVariant`, `Channel`, `Cart`, and `Staff` use `SoftDeletes`. For products and channels in particular this is functioning as "archived" — rows are tombstoned, hidden from the storefront, but kept around for orders and reporting. That conflates two things (an admin lifecycle and a real deletion) and forces every query to remember `withTrashed()` semantics.
- **No coordination between order sub-statuses.** Real-world orders track payment progress and fulfilment progress independently, and the customer-facing status is a function of both. Today's single `status` string collapses that into one ad-hoc field that the admin updates by hand.
- **No transition events.** Nothing in core dispatches an event when an order's status changes, so notifications, webhook fan-out, and downstream cache invalidation each have to roll their own listener wired off Eloquent `updated`.

Net effect: states are stringly-typed, illegal transitions cannot be prevented, "archived" is smuggled in via soft-deletes, and orders carry one status where they need three.

## Proposal

Adopt `spatie/laravel-model-states` v2 for every lifecycle column in core. Replace soft-deletes used as archive flags with explicit `Archived` states. For orders, introduce three coordinated state machines — `PaymentState`, `FulfilmentState`, `OrderState` — plus a resolver that derives the order status from the other two. All state classes live under `Lunar\Core\States\…`.

### A. Dependency

Add `spatie/laravel-model-states: ^2` to `packages/core/composer.json`. Pinned to v2 because v1 lacks the typed `transitionableStates()` config that this spec relies on.

### B. Directory layout under `packages/core/src/`

```
Enums/
└── StateCategory.php
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
    ├── OrderState.php
    ├── PaymentState.php
    ├── FulfilmentState.php
    ├── OrderStateConfig.php           ← contract, moves to Contracts/ (see §E)
    ├── DefaultOrderStateConfig.php
    ├── Order/                         ← concrete OrderState classes
    │   ├── AwaitingPayment.php
    │   ├── PaymentFailed.php
    │   ├── Backordered.php
    │   ├── InProcess.php
    │   ├── PartiallyShipped.php
    │   ├── Shipped.php
    │   ├── Complete.php
    │   ├── Returned.php
    │   ├── Refunded.php
    │   ├── OnHold.php
    │   └── Cancelled.php
    ├── Payment/                       ← concrete PaymentState classes
    │   ├── Pending.php
    │   ├── Authorized.php
    │   ├── Captured.php
    │   ├── Failed.php
    │   └── Refunded.php
    └── Fulfilment/                    ← concrete FulfilmentState classes
        ├── Unfulfilled.php
        ├── Backordered.php
        ├── Processing.php
        ├── PartiallyShipped.php
        ├── Shipped.php
        ├── Delivered.php
        └── Returned.php
```

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

### D. Order — three coordinated machines

#### Columns

Baseline migration `2026_01_01_000028_create_orders_table.php` is edited in place (v2 is pre-release; same rule applied by specs 0017, 0018, 0019):

- Drop the existing `string('status')->index()`.
- Add:
  ```php
  $table->string('payment_status')->default('pending')->index();
  $table->string('fulfilment_status')->default('unfulfilled')->index();
  $table->string('order_status')->default('awaiting-payment')->index();
  ```

Factories under `packages/core/src/Database/Factories/OrderFactory.php` emit the three new columns; the old single-column states (`payment-received`, `dispatched`, etc.) are deleted from any factory state methods.

#### `Enums\StateCategory`

Categorises a payment or fulfilment state for the resolver matrix:

```php
namespace Lunar\Core\Enums;

enum StateCategory
{
    case Pending;
    case Active;
    case Complete;
    case Blocked;
    case Failed;
}
```

#### `PaymentState` (abstract)

```php
abstract public function label(): string;
abstract public function category(): StateCategory;
```

| State        | `$name`      | `category()`            |
|--------------|--------------|-------------------------|
| `Pending`    | `pending`    | `Pending`               |
| `Authorized` | `authorized` | `Active`                |
| `Captured`   | `captured`   | `Complete`              |
| `Failed`     | `failed`     | `Failed`                |
| `Refunded`   | `refunded`   | `Complete`              |

Transitions: `Pending → Authorized`, `Pending → Captured`, `Pending → Failed`, `Authorized → Captured`, `Authorized → Failed`, `Captured → Refunded`, `Failed → Pending`. Default: `Pending`.

#### `FulfilmentState` (abstract)

Same shape — `label()` + `category()`.

| State              | `$name`             | `category()` |
|--------------------|---------------------|--------------|
| `Unfulfilled`      | `unfulfilled`       | `Pending`    |
| `Backordered`      | `backordered`       | `Blocked`    |
| `Processing`       | `processing`        | `Pending`    |
| `PartiallyShipped` | `partially-shipped` | `Active`     |
| `Shipped`          | `shipped`           | `Active`     |
| `Delivered`        | `delivered`         | `Complete`   |
| `Returned`         | `returned`          | `Failed`     |

Transitions: `Unfulfilled → Processing`, `Unfulfilled → Backordered`, `Backordered → Processing`, `Processing → Shipped`, `Processing → PartiallyShipped`, `PartiallyShipped → Shipped`, `Shipped → Delivered`, `Shipped → Returned`, `Delivered → Returned`. Default: `Unfulfilled`.

#### `OrderState` (abstract)

```php
abstract public function label(): string;
public function isManualOverride(): bool { return false; }
```

| State              | `$name`             | Manual override |
|--------------------|---------------------|-----------------|
| `AwaitingPayment`  | `awaiting-payment`  | false           |
| `PaymentFailed`    | `payment-failed`    | false           |
| `Backordered`      | `backordered`       | false           |
| `InProcess`        | `in-process`        | false           |
| `PartiallyShipped` | `partially-shipped` | false           |
| `Shipped`          | `shipped`           | false           |
| `Complete`         | `complete`          | false           |
| `Returned`         | `returned`          | false           |
| `Refunded`         | `refunded`          | false           |
| `OnHold`           | `on-hold`           | **true**        |
| `Cancelled`        | `cancelled`         | **true**        |

`OrderState` registers **no** transitions — the resolver writes it directly and `saveQuietly()` bypasses the model-states transition guard. Default: `AwaitingPayment`. `isManualOverride()` returning `true` parks the order: see §F.

### E. `Contracts\OrderStateConfig`

```php
namespace Lunar\Core\Contracts;

use Lunar\Core\States\Order\FulfilmentState;
use Lunar\Core\States\Order\OrderState;
use Lunar\Core\States\Order\PaymentState;

interface OrderStateConfig
{
    /** @return array<class-string<PaymentState>> */
    public function paymentStates(): array;

    /** @return array<class-string<FulfilmentState>> */
    public function fulfilmentStates(): array;

    /** @return array<class-string<OrderState>> */
    public function orderStates(): array;

    /** @return array<class-string<PaymentState>, list<class-string<PaymentState>>> */
    public function paymentTransitions(): array;

    /** @return array<class-string<FulfilmentState>, list<class-string<FulfilmentState>>> */
    public function fulfilmentTransitions(): array;

    /** @return class-string<PaymentState> */
    public function defaultPaymentState(): string;

    /** @return class-string<FulfilmentState> */
    public function defaultFulfilmentState(): string;

    /** @return class-string<OrderState> */
    public function defaultOrderState(): string;

    /** @return class-string<OrderState> */
    public function resolveOrderState(PaymentState $payment, FulfilmentState $fulfilment): string;
}
```

The abstract State base classes (`PaymentState`, `FulfilmentState`, `OrderState`) read from the bound `OrderStateConfig` to register their states and transitions. This is the **single seam** for adding bespoke order states: a downstream consumer extends `DefaultOrderStateConfig`, adds states + transitions, and binds the subclass in their service provider. The state machine and the resolver matrix both pick up the new states without any core change.

```php
class MyOrderStateConfig extends DefaultOrderStateConfig
{
    public function paymentStates(): array
    {
        return [...parent::paymentStates(), PartiallyCaptured::class];
    }

    public function paymentTransitions(): array
    {
        return [
            ...parent::paymentTransitions(),
            Captured::class => [PartiallyCaptured::class, ...parent::paymentTransitions()[Captured::class]],
            PartiallyCaptured::class => [Captured::class],
        ];
    }

    public function resolveOrderState(PaymentState $payment, FulfilmentState $fulfilment): string
    {
        // Map the new payment state into an existing order state, or add an
        // override before delegating.
        return parent::resolveOrderState($payment, $fulfilment);
    }
}
```

Bind in a service provider's `register()` (not `boot()`) so the catalogue is in place before any model uses the state casts. Spatie's `State` base caches the resolved state mapping per class for the lifetime of the process — under Laravel Octane this cache survives between requests, so runtime rebinding is not supported. Sub-states aren't modelled hierarchically: `StateCategory` (`Pending / Active / Complete / Blocked / Failed`) is the grouping mechanism, and new states classify themselves into an existing category via `category()`.

`DefaultOrderStateConfig` implements the contract. Resolution rules, in order:

1. **Refunded payment** → `OrderRefunded`, regardless of fulfilment.
2. **Override map lookup** — `"{paymentClass}|{fulfilmentClass}"` keyed map, used for the partial-shipment cases listed below.
3. **Category-pair match** — `(payment->category(), fulfilment->category())` falls through a `match` table.

```php
public function resolveOrderState(PaymentState $payment, FulfilmentState $fulfilment): string
{
    if ($payment instanceof Refunded) {
        return OrderRefunded::class;
    }

    $key = $payment::class.'|'.$fulfilment::class;
    if (isset($this->overrides()[$key])) {
        return $this->overrides()[$key];
    }

    return match ($payment->category()) {
        StateCategory::Failed  => OrderPaymentFailed::class,
        StateCategory::Pending => AwaitingPayment::class,
        StateCategory::Active, StateCategory::Complete => match ($fulfilment->category()) {
            StateCategory::Blocked  => OrderBackordered::class,
            StateCategory::Pending  => InProcess::class,
            StateCategory::Active   => OrderShipped::class,
            StateCategory::Complete => OrderComplete::class,
            StateCategory::Failed   => OrderReturned::class,
        },
        default => AwaitingPayment::class,
    };
}

/** @return array<string, class-string<OrderState>> */
protected function overrides(): array
{
    return [
        Captured::class.'|'.FulfilmentPartiallyShipped::class    => OrderPartiallyShipped::class,
        Authorized::class.'|'.FulfilmentPartiallyShipped::class  => OrderPartiallyShipped::class,
    ];
}
```

Bind in `LunarServiceProvider::registerServices()` (the spec 0016 home for non-action service bindings):

```php
$this->app->bind(OrderStateConfig::class, DefaultOrderStateConfig::class);
```

`OrderStateConfig` is **not** an action — it stays out of `ActionServiceProvider::$actions`. Consumers swap the binding in their own provider to extend the resolver.

### F. Order::computeOrderStatus()

```php
public function computeOrderStatus(): void
{
    if ($this->order_status->isManualOverride()) {
        return;
    }

    $config = app(OrderStateConfig::class);
    $newStateClass = $config->resolveOrderState($this->payment_status, $this->fulfilment_status);

    $previousValue = $this->getRawOriginal('order_status');
    $newValue      = $newStateClass::getMorphClass();

    if ($previousValue === $newValue) {
        return;
    }

    // saveQuietly bypasses the observer; syncOriginal() has not yet run when
    // `updated()` fires, so a nested save() would see payment_status/fulfilment_status
    // as still dirty and recurse indefinitely.
    $this->forceFill(['order_status' => $newStateClass]);
    $this->saveQuietly();

    OrderStatusUpdated::dispatch($this, $previousValue, $this->order_status->getValue());
}
```

The `app(OrderStateConfig::class)` resolution is the one acknowledged exception to the spec 0016 "no service location inside service methods" rule — `Order` is an Eloquent model, not a service-layer class, and threading the config through every save path is more contagious than the lookup. The rule still applies to every class under `Actions/`, `Managers/`, `Drivers/`, `Generators/`, `Orders/`, `Pricing/`, `Validation/`, `Telemetry/`, `Listeners/`, `Observers/`, `Pipelines/`.

Casts on `Order`:

```php
'payment_status'    => PaymentState::class,
'fulfilment_status' => FulfilmentState::class,
'order_status'      => OrderState::class,
```

PHPDoc: `@property PaymentState $payment_status`, `@property FulfilmentState $fulfilment_status`, `@property OrderState $order_status`.

### G. `OrderObserver` — rewrite

Replaces the existing `Observers/OrderObserver.php` (which only logs status-change activity today).

```php
public function updating(Order $order): void
{
    if ($order->isDirty('order_status')) {
        activity()
            ->causedBy(auth()->user())
            ->performedOn($order)
            ->event('status-update')
            ->withProperties([
                'new'      => $order->order_status->getValue(),
                'previous' => $order->getOriginal('order_status'),
            ])
            ->log('status-update');
    }
}

public function updated(Order $order): void
{
    if ($order->wasChanged('payment_status') || $order->wasChanged('fulfilment_status')) {
        // computeOrderStatus() uses saveQuietly() internally and dispatches
        // OrderStatusUpdated itself — do not also check wasChanged('order_status').
        $order->computeOrderStatus();
    } elseif ($order->wasChanged('order_status')) {
        OrderStatusUpdated::dispatch(
            $order,
            $order->getOriginal('order_status'),
            $order->order_status->getValue(),
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

class OrderStatusUpdated
{
    use Dispatchable;

    public function __construct(
        public Order $order,
        public string $previousStatus,
        public string $newStatus,
    ) {}
}
```

### I. `Listeners\SendOrderStatusNotifications`

```php
public function handle(OrderStatusUpdated $event): void
{
    $notifications = config(
        "lunar.sales.orders.statuses.{$event->newStatus}.notifications",
        []
    );

    foreach ($notifications as $class) {
        $event->order->notify(new $class($event->order));
    }
}
```

Wired in `LunarServiceProvider::bootingPackage()`:

```php
Event::listen(OrderStatusUpdated::class, SendOrderStatusNotifications::class);
```

The listener has no collaborators today; if it grows (e.g. resolving the notifiable from the customer rather than the order), it gains them via constructor injection per spec 0016.

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

`OrderStateConfig::paymentStates()` / `fulfilmentStates()` / `orderStates()` give the admin and the storefront API a typed list of available states. The Filament admin uses these to populate the payment/fulfilment status select inputs in place of the hardcoded `config('lunar.orders.statuses')` array. Migrating those config arrays into the state classes themselves is part of stage 2 (admin retarget).

## Alternatives considered

- **Keep string statuses, add validation rules.** Rejected: validation gates the write but does nothing for transition enforcement, manual overrides, or computed statuses. Spatie's model-states already covers all three and is in heavy use across the wider Laravel ecosystem.
- **Roll a bespoke state library.** Rejected: model-states v2 is small, well-typed, supports `saveQuietly()`/`forceFill()` cleanly, integrates with Eloquent casts, and is supported. Reinventing it is pure cost.
- **Keep soft-deletes, add a parallel `archived_at`.** Rejected: that gives you two ways to hide a record and forces every query to remember both. The point of the redesign is one explicit lifecycle column per model.
- **Make `OrderState` itself responsible for transitions out of `payment_status`/`fulfilment_status`.** Rejected: that hides the resolver behind the state-machine API and makes it harder to extend. Keeping the resolver as a separate, container-bound service (the `OrderStateConfig` seam) is the spec 0016 way.
- **Skip the override map; rely solely on the category matrix.** Rejected: `PartiallyShipped` is genuinely a derived order status that the category matrix cannot express without conflating it with `Shipped`. A small, explicit override map is clearer than reshaping the matrix.
- **Drop the `Cancelled` / `OnHold` manual-override states and use a separate `cancelled_at` column.** Rejected: it splits the lifecycle in two and means the admin has to consult two fields to know what an order is doing. `isManualOverride()` keeps everything on `order_status`.

## Migration impact

- **Baseline migrations edited in place.** Per the spec 0019 precedent, v2 is unreleased and the baseline is still being shaped, so the relevant `2026_01_01_*` files are edited:
  - `..._create_orders_table.php` — drop `status`, add `payment_status` / `fulfilment_status` / `order_status` columns with the defaults above.
  - `..._create_products_table.php` — drop `softDeletes()` and the `deleted_at` index; `status` stays but now stores `ProductState::$name` values (`draft`, `published`, `archived`).
  - `..._create_collections_table.php` — drop `softDeletes()`; add a `status` string column (collections do not have one today).
  - `..._create_channels_table.php` — drop `softDeletes()`; add a `status` string column (channels do not have one today; the `enabled` cast in `Models/Channel.php:35` is removed alongside).
  - Once v2 ships, this rule flips back: subsequent state additions go in new migrations.
- **No core data migration.** v2 has no live data to convert.
- **Data migration (stage 3, `packages/upgrade`).** Add one-way migrations to:
  - Map v1 `products.status` values (`published` / `draft`) to v2 (`published` / `draft`); v1 trashed products → `archived`. Null the `deleted_at` column afterwards. ([[feedback-upgrade-migrations-no-down]])
  - Map v1 `channels.enabled` boolean → `status` (`active` / `inactive`); null `deleted_at`.
  - Collections: trashed → `archived`, otherwise `published` (collections were not lifecycle-gated in v1).
  - Map v1 `orders.status` to the new triple. Reasonable defaults: `awaiting-payment` → (`pending`, `unfulfilled`, `awaiting-payment`); `payment-received` → (`captured`, `unfulfilled`, `in-process`); `dispatched` → (`captured`, `shipped`, `shipped`); `cancelled` → (`pending`, `unfulfilled`, `cancelled` with manual override). The full mapping table is finalised in the upgrade package PR.
- **Breaking changes to the public contract surface:**
  - `Models\Order::$status` becomes three columns. PHPDoc and the `casts()` array change.
  - `Models\Product`, `ProductVariant`, `Channel`, `Collection` lose `SoftDeletes`; any caller using `withTrashed()` / `onlyTrashed()` / `restore()` breaks. Rector rule: replace `->onlyTrashed()` with `->where('status', Archived::$name)` where the intent is "show archived". `->restore()` becomes `->status->transitionTo(Draft::class)` (or `Active::class` for channels).
  - `Models\Channel` loses the `enabled` boolean cast.
  - `Models\Order::statusLabel()` becomes `$order->order_status->label()`. Add a Rector rule for the rename.
  - `config('lunar.orders.statuses')` is no longer the source of truth for available statuses (the state classes are); notification configuration moves to `lunar.sales.orders.statuses.{name}.notifications`. Document the relocation.
  - These are listed under §J of the `LunarSetList` in the `upgrade` package.
- **Upgrade path for v1.x consumers (stage 3).** Rector rules cover the renames/removals above. Data migration covers the column changes. Soft-delete removal is one-way — restoring trashed rows must happen *before* the v2 upgrade runs.
- **Translation / locale impact.** Each state's `label()` returns a translation key (e.g. `lunar::states.product.published`). New keys land in all 16 locales under each package's `resources/lang/` — English first, mirrored placeholders for the other 15. Affected keys:
  - `states.product.{draft,published,archived}`
  - `states.collection.{draft,published,archived}`
  - `states.channel.{active,inactive}`
  - `states.payment.{pending,authorized,captured,failed,refunded}`
  - `states.fulfilment.{unfulfilled,backordered,processing,partially-shipped,shipped,delivered,returned}`
  - `states.order.{awaiting-payment,payment-failed,backordered,in-process,partially-shipped,shipped,complete,returned,refunded,on-hold,cancelled}`
- **Filament / admin impact.** The order resource currently renders a single status badge and a single status select. Update to:
  - Three badges (payment / fulfilment / order), with order styled as the "computed" one (visually distinct).
  - Payment and fulfilment selects driven by `OrderStateConfig::paymentStates()` / `fulfilmentStates()`, gated by `transitionableStates()` so the UI only offers legal next states.
  - "Place on hold" / "Cancel" actions that transition `order_status` directly to `OnHold` / `Cancelled` (the manual-override states); a "Resume" action that transitions out of them, after which `computeOrderStatus()` reasserts the computed value.
  - Product / collection list filters add a "Status" select (`Draft / Published / Archived`); the soft-delete filter is removed.
  - Channel switcher hides `Inactive` channels by default.
  - Verify end-to-end against the host app at `https://lunar-v2.test` (Herd) per the package convention.

## Acceptance checks

Feature and unit tests in `packages/lunar/tests/core/Unit/States/` and `tests/core/Feature/Orders/`:

- Each simple machine (Channel, Product, Collection) has one passing test per allowed transition and one failing test (`CouldNotPerformTransition`) per disallowed transition; defaults assert on factory-created models.
- `PaymentStateTransitionsTest` and `FulfilmentStateTransitionsTest` cover every transition from §D, plus a couple of disallowed ones.
- `DefaultOrderStateConfigTest` unit-tests the resolver matrix directly (no DB) for at least: pending+unfulfilled→awaiting-payment, failed+anything→payment-failed, authorized/captured+backordered→backordered, captured+unfulfilled→in-process, captured+shipped→shipped, captured+partially-shipped→partially-shipped (override), authorized+partially-shipped→partially-shipped (override), captured+delivered→complete, captured+returned→returned, refunded+anything→refunded.
- Integration: changing `payment_status` on a real `Order` recomputes `order_status` and dispatches `OrderStatusUpdated` exactly once.
- Integration: a manual-override status (`OnHold`, `Cancelled`) blocks recomputation; transitioning back to an automated state resumes it.
- Activity log: writing a new `order_status` produces a `status-update` entry with `new` / `previous` properties.
- `whereVisible()` on `Product` / `Collection` returns only `Published` rows.
- `ArchitectureTest` extension: every state class extends its abstract base and exposes a static `$name`; every `States/` subfolder has an `*State` abstract.

## Open questions

- **Notification config relocation** — moving from `lunar.orders.statuses` to `lunar.sales.orders.statuses` matches where `sales.orders.*` already lives in `packages/core/config/`. Confirm during implementation that no downstream package reads the old key directly.
- **`OrderState` carrying no transitions** — relies on `saveQuietly()` + `forceFill()` to bypass the model-states transition guard. If model-states v2 changes that behaviour, fall back to registering "everything to everything" transitions on `OrderState`.
- **Order-line FK on archived products** — `order_lines.purchasable_*` already snapshots the data needed to render historical orders, but a quick audit during stage 1 should confirm there is no read path that lazily re-resolves through the morph and assumes the product still exists.
- **Cart and Staff soft-deletes** — left in for now (§J). Whether `Cart` adopts its own state machine (`Active / Abandoned / Converted`) is a follow-up, not part of this spec.

## References

- [[0013-base-directory-reorganisation]] — `States/` is a new top-level folder following the same one-concern-per-folder rule; `Contracts/Orders/OrderStateConfig` follows the no-`Interface`-suffix rule.
- [[0016-service-layer-di]] — `OrderStateConfig` is bound in `LunarServiceProvider::registerServices()`; the only acknowledged service-location is inside `Order::computeOrderStatus()` (an Eloquent model, not a service-layer class) and is called out explicitly.
- [[0009-filament-actions-and-global-search]] — existing `BulkPublish` / `BulkUnpublish` / `BulkArchive` actions retarget onto `ProductState` transitions.
- `spatie/laravel-model-states` v2 — https://spatie.be/docs/laravel-model-states/v2
- `packages/upgrade` — Rector rules and v1 → v2 data migrations for the column / API changes above.
