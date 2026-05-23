# 0009 — Filament-native verbs and discoverability (actions library + global search)

- Status: implemented
- Author: Glenn Jacobs
- Created: 2026-05-23
- TODO item: "Filament-native verbs and discoverability — actions library + global search"

## Problem

The `lunarphp/filament` bridge package now ships the nouns of a commerce admin — schemas, tables, infolists, selectors, widgets, relation managers, field types. A Filament developer can compose every visible surface from our parts.

What it does not ship are the **verbs** (refund, capture, fulfil, mark shipped, duplicate, bulk publish, adjust stock) or the **discovery layer** (Filament's global search). Both are first-class Filament concepts and both currently live behind the admin shell rather than inside the bridge.

### Verbs: actions live inside admin pages, not the bridge

Commerce is verb-heavy. Every well-built admin has a one-click "refund this transaction", "capture pending payment", "mark as fulfilled", "duplicate this product", "publish 24 selected products" path. Today Lunar's verbs all live as inline closures inside `lunarphp/admin` page classes:

- `packages/admin/src/Filament/Resources/OrderResource/Pages/ManageOrder.php:380` — `getRefundAction()` builds a 70-line `Action::make('refund')` inline.
- `packages/admin/src/Filament/Resources/OrderResource/Pages/ManageOrder.php` — `getCaptureAction()` does the same for captures.
- `packages/admin/src/Support/Actions/Orders/UpdateStatusAction.php` and `UpdateStatusBulkAction.php` — exist as classes, but inside `lunarphp/admin`, not the bridge. The bridge has a copy of `UpdateStatusBulkAction` under `packages/filament/src/Tables/Actions/Orders/` that the table references — already drifting.
- `packages/admin/src/Support/Actions/Collections/*` and `packages/filament/src/Actions/Collections/*` — both packages ship parallel `MoveCollection`/`CreateRootCollection`/`CreateChildCollection`/`DeleteCollection` classes. The bridge versions are the ones actually wired into `CollectionTreeView`; the admin copies appear unused.

The consequences mirror the pre-0008 selector problem:

1. **Inconsistent verb surface.** A consumer building a Filament panel without our admin shell has no `RefundOrderAction::make()` to drop into their own order page. They have to copy 70 lines of inline closure out of `ManageOrder` and maintain it themselves.
2. **Duplication and drift.** `UpdateStatusBulkAction` already exists in two places. Collection actions ditto. Every commerce verb we add risks landing in three places (admin, bridge, downstream consumer).
3. **The bridge package is incomplete.** Spec 0006 promised the bridge would let a downstream developer compose Lunar pieces into their own panel. Schemas and tables ship. Actions don't. A consumer can build a `MyOrderResource` that uses `OrderForm::configure(...)` and `OrderTable::configure(...)`, but the moment they want a refund button they're back to copying admin code.

### Discoverability: global search is wired in admin resources, not exposed by the bridge

Filament v5's global search bar at the top of every panel is one of the highest-leverage UX features. Lunar implements it today, but only inside `lunarphp/admin`:

- `packages/admin/src/Support/Resources/Concerns/HasScoutGlobalSearch.php` — the Scout-aware backend (prefers `Model::search()` when both Scout is enabled and the model uses `Searchable`, else falls back to translated-attribute SQL search).
- `packages/admin/src/Support/Resources/BaseResource.php` — applies the trait to every Lunar resource.
- `packages/admin/src/Filament/Resources/{Product,Order,Customer,Collection,Brand}Resource.php` — each defines `getGloballySearchableAttributes()`, `getGlobalSearchResultDetails()`, `getGlobalSearchResultUrl()`, `getGlobalSearchEloquentQuery()` directly inline.

A consumer who composes their own `MyProductResource` using `ProductForm` and `ProductTable` from the bridge gets the form and the table — but no global search rows for products. They have to re-discover and re-implement the four `getGlobalSearch...` methods, plus re-build the Scout-aware backend, on every resource. That is exactly the duplication the bridge package exists to remove.

Note the symmetry with spec 0008: the same `RecordSearch` service the selectors lean on (`Lunar\Filament\Forms\Components\Support\RecordSearch`) is *also* the Scout-vs-translated-attribute fallback that global search needs. Today the selectors use `RecordSearch`; the global search uses `HasScoutGlobalSearch`. One backend, two call paths. That should collapse to one.

## Proposal

Ship two new public surfaces in `lunarphp/filament`:

1. **A first-party actions library** — Filament `Action` subclasses for every commerce verb the admin uses today, under `Lunar\Filament\Actions\…`. Drop-in replacements for the inline closures and the duplicated `Support/Actions` classes.
2. **A global-search contract** — per-model search descriptors (searchable fields, result details, result URL) registered on the bridge, plus a reusable `HasLunarGlobalSearch` trait that consumers mix into their own resources to get one-line wiring. The Scout-vs-translated-attribute backend collapses onto the existing `RecordSearch` service from spec 0008.

The two ship together because they share the same theme — "a Filament dev using the bridge without our admin shell should not have to re-implement Lunar's verbs or Lunar's search" — and because the global-search surface depends on the `RecordSearch` consolidation that makes the actions library cleaner to test.

### Part A — Actions library

#### The boundary: Filament actions are thin shells

The actions added by this spec are **Filament UI wrappers**. They own the modal schema, the labels, the confirmation copy, the notification, and the `visible()` predicate. They do **not** own business logic.

A core action is warranted when the verb involves any of:

- domain validation that needs to be enforced regardless of caller (e.g. "refund amount cannot exceed available to refund");
- multi-step or cross-aggregate state changes (e.g. duplicating a product also duplicates its variants, prices, options);
- side effects worth dispatching from anywhere (events, activity log, notifications, status transitions);
- arithmetic that the model itself doesn't already expose (e.g. computing available-to-refund from charges minus refunds).

A core action is **not** warranted when the verb is:

- purely presentational (rendering a PDF, streaming an export, opening a modal);
- a single-field write the model already exposes safely (`$model->update(['notes' => $value])` is fine — no core action needed unless we want event/activity-log side effects);
- a Filament-only UX affordance (showing a confirmation, copying to clipboard, navigating elsewhere).

When a Filament action delegates to a core action, the Filament layer has no knowledge of refund maths, status transition rules, stock arithmetic, product replication semantics, or pricing — it collects user input from the modal, hands it to the core action, and presents the result.

Core actions return whatever shape is natural for the operation: a record, a boolean, a value object, `void`. The Filament action knows what its core counterpart returns and translates that into a notification. No forced result envelope.

```
Filament\Actions\Orders\RefundOrderAction        →   Core\Actions\Orders\RefundOrder           (validation + transaction dispatch + events)
Filament\Actions\Orders\CaptureOrderAction       →   Core\Actions\Orders\CaptureOrder          (validation + transaction dispatch + events)
Filament\Actions\Orders\UpdateOrderStatusAction  →   Core\Actions\Orders\UpdateOrderStatus     (status transition seam for future state machine)
Filament\Actions\Orders\MarkOrderAsShippedAction →   Core\Actions\Orders\MarkOrderAsShipped    (sets shipped_at + dispatches notification)
Filament\Actions\Orders\AddOrderNoteAction       →   Filament-only                              (single-field write; relies on Order::saved event for audit)
Filament\Actions\Products\DuplicateProductAction →   Core\Actions\Products\DuplicateProduct    (cross-aggregate replication)
Filament\Actions\Products\PublishProductsBulkAction →  Core\Actions\Products\UpdateProductStatus (status transition seam, run in bulk)
Filament\Actions\Products\UnpublishProductsBulkAction → Core\Actions\Products\UpdateProductStatus (status transition seam, run in bulk)
Filament\Actions\Products\ArchiveProductsBulkAction →  Core\Actions\Products\UpdateProductStatus (status transition seam, run in bulk)
Filament\Actions\Products\AdjustStockAction      →   Core\Actions\Products\AdjustStock         (validation + activity log entry)
Filament\Actions\Products\MapVariantsToProductOptionsAction → Core\Actions\Products\MapVariantsToProductOptions  (cross-aggregate mapping)
Filament\Actions\Collections\MoveCollectionAction     → Core\Actions\Collections\MoveCollection (lft/rgt recompute + descendant validation)
Filament\Actions\Collections\CreateRootCollectionAction → Core\Actions\Collections\CreateRootCollection (group idempotency)
Filament\Actions\Collections\CreateChildCollectionAction → Core\Actions\Collections\CreateChildCollection (parent linkage + position)
Filament\Actions\Collections\DeleteCollectionAction    → Core\Actions\Collections\DeleteCollection (descendant re-parent handling)
Filament\Actions\Orders\DownloadOrderPdfAction   →   Filament-only                              (rendering is UI)
```

Each row above carries the reason a core action exists. `AddOrderNoteAction` is a single-field write with no special invariants — kept in Filament. If we later want events or activity-log side effects on notes specifically, the action graduates to core.

#### What lands in core as part of this spec

The following core action classes do **not** exist today (or exist in the wrong package) and ship with this spec. They all extend `Lunar\Core\Actions\AbstractAction` and follow the existing `make()` / `execute(...)` / `run(...)` convention.

| Class | Responsibility | Replaces |
| --- | --- | --- |
| `Core\Actions\Orders\RefundOrder` | Validate refund amount against `availableToRefund`, call `Transaction::refund()`, return result | inline `getRefundAction()` closure + `availableToRefund`/`canBeRefunded` computed properties in `ManageOrder.php` |
| `Core\Actions\Orders\CaptureOrder` | Same shape for captures (validate against `availableToCapture`, call `Transaction::capture()`) | inline `getCaptureAction()` closure in `ManageOrder.php` |
| `Core\Actions\Orders\UpdateOrderStatus` | Apply a status change (fires events, runs hooks). Foundation for the state-machine TODO item — replaced by a state-machine transition once that lands | `Admin\Support\Actions\Orders\UpdateStatusAction`'s inline status-mutation logic |
| `Core\Actions\Orders\MarkOrderAsShipped` | Convenience over `UpdateOrderStatus` for the shipped transition (records shipped_at, dispatches notification, etc.) | nothing today — bespoke flow that consumers re-implement |
| `Core\Actions\Products\DuplicateProduct` | Replicate a product with its variants, prices, options, attributes, and translated names (with collision-safe name suffixing) | inline closures in downstream consumers; no in-repo implementation today |
| `Core\Actions\Products\UpdateProductStatus` | Apply a status change to one or many products. Hook for the products state-machine TODO (`draft → published → archived`) | nothing today — admin writes `$product->status = …` ad-hoc |
| `Core\Actions\Products\AdjustStock` | Apply a stock delta with reason and audit entry against the existing `stock` / `backorder` columns. Superseded by the Inventory work | ad-hoc `$variant->stock = …` writes |
| `Core\Actions\Products\MapVariantsToProductOptions` | Lift the existing logic out of `Admin\Actions\Products\MapVariantsToProductOptions` / `Filament\Actions\Products\MapVariantsToProductOptions` into core | the two duplicate copies in admin and filament |
| `Core\Actions\Collections\MoveCollection` | Re-parent a collection, recompute lft/rgt, validate against descendants | inline closure in admin |
| `Core\Actions\Collections\CreateRootCollection` | Create a root collection (idempotent against group) | inline closure in admin |
| `Core\Actions\Collections\CreateChildCollection` | Create a child under a parent | inline closure in admin |
| `Core\Actions\Collections\DeleteCollection` | Delete with optional descendant re-parent target | inline closure in admin |

`Transaction::refund()` and `Transaction::capture()` already live on the core `Transaction` model and stay there. The new `Core\Actions\Orders\RefundOrder` / `CaptureOrder` call into them — they do not duplicate the payment-driver dispatch.

The state-related core actions (`UpdateOrderStatus`, `UpdateProductStatus`) are deliberately thin in v2. They exist so the Filament layer has a stable entry point; once the state-machines TODO item lands they get re-implemented internally as state-machine transitions without changing the public surface.

#### Test surface for core actions

Each new core action ships with a feature test in `tests/Core/Actions/` covering the happy path, the validation failure (e.g. refund-amount-exceeds-available), and the event emission. The Filament action tests in turn only assert the modal schema, the Filament-level `visible()`/`failureNotification()` plumbing, and that the core action is called with the right arguments — not the underlying business behaviour.

This split means a consumer who builds their own UI on top of `Core\Actions\Orders\RefundOrder` gets the same guarantees as our Filament action, and our Filament tests stay fast and focused.

#### Package layout

```
packages/filament/src/Actions/
    Orders/
        RefundOrderAction.php          // existing inline closure → first-class action
        CaptureOrderAction.php         // existing inline closure → first-class action
        UpdateOrderStatusAction.php    // moved from admin/Support/Actions/Orders + dedup
        UpdateOrderStatusBulkAction.php // moved from Tables/Actions/Orders + dedup
        MarkOrderAsShippedAction.php   // new (was: change status manually)
        AddOrderNoteAction.php         // new
        DownloadOrderPdfAction.php     // moved from admin/Support/Actions/PdfDownload (renamed)
    Products/
        DuplicateProductAction.php       // new
        PublishProductsBulkAction.php    // new
        UnpublishProductsBulkAction.php  // new
        ArchiveProductsBulkAction.php    // new
        AdjustStockAction.php            // new (single-line stock edit; full inventory is a separate spec)
        MapVariantsToProductOptionsAction.php // existing, lift into Actions/ namespace
    Collections/
        CreateRootCollectionAction.php
        CreateChildCollectionAction.php
        MoveCollectionAction.php
        DeleteCollectionAction.php
    Concerns/
        InteractsWithTransactions.php   // shared between Refund and Capture
        ConfirmsDestructiveAction.php   // shared "type CONFIRM to proceed" pattern
```

Namespace: `Lunar\Filament\Actions\…` (matches the existing `Lunar\Filament\Forms\Components\…` shape).

#### Action shape

Every action follows the same surface so call sites are predictable, and every action is a single class — no traits-mixed-into-pages, no inline closures.

```php
namespace Lunar\Filament\Actions\Orders;

use Filament\Actions\Action;
use Lunar\Core\Actions\Orders\RefundOrder;
use Lunar\Core\Models\Order;

class RefundOrderAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'refund';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('lunar-filament::actions.orders.refund.label'))
            ->icon('heroicon-o-backward')
            ->color('warning')
            ->schema(fn (Order $record) => $this->getRefundSchema($record))
            ->action(fn (array $data, Order $record) => $this->performRefund($data, $record))
            ->visible(fn (Order $record) => RefundOrder::canRun($record))
            ->successNotificationTitle(__('lunar-filament::actions.orders.refund.notification.success'))
            ->failureNotificationTitle(__('lunar-filament::actions.orders.refund.notification.error'));
    }

    protected function performRefund(array $data, Order $record): void
    {
        // Filament responsibility: form-state translation + presentation outcomes.
        // Business logic (validation, refund execution, event emission) lives in RefundOrder.
        $result = RefundOrder::run(
            order: $record,
            transactionId: $data['transaction'],
            amount: $data['amount'],
            notes: $data['notes'] ?? null,
        );

        if (! $result->success) {
            $this->failureNotification(
                fn () => Notification::make('refund_failure')->color('danger')->title($result->message)
            );
            $this->failure();
            $this->halt();
        }
    }

    // protected helpers — getRefundSchema, canRefund — overridable in subclasses
}
```

The action class contains zero business rules. `RefundOrder::canRun()` decides whether a refund is possible (used by `visible()`). `RefundOrder::run()` does the work and returns a result object. Swapping the Filament UI for a different admin framework, an API endpoint, or a CLI command does not touch refund logic.

Call site:

```php
use Lunar\Filament\Actions\Orders\RefundOrderAction;

protected function getDefaultHeaderActions(): array
{
    return [
        CaptureOrderAction::make(),
        RefundOrderAction::make(),
        UpdateOrderStatusAction::make(),
    ];
}
```

That replaces the 70-line `getRefundAction()` closure in `ManageOrder.php`. Subclasses override the `protected` helpers when a consumer needs to bend behaviour without re-implementing the whole action.

#### Bulk vs single

Every action that makes sense in bulk gets a sibling `…BulkAction` class extending `Filament\Actions\BulkAction`. They share `Concerns/…` traits for the per-record body so behaviour stays in sync.

| Single | Bulk | Lives in |
| --- | --- | --- |
| `UpdateOrderStatusAction` | `UpdateOrderStatusBulkAction` | header/row + table |
| `MarkOrderAsShippedAction` | `MarkOrdersAsShippedBulkAction` | header/row + table |
| `DuplicateProductAction` | — | row |
| `PublishProductsBulkAction` | (bulk-only) | table |
| `UnpublishProductsBulkAction` | (bulk-only) | table |
| `ArchiveProductsBulkAction` | (bulk-only) | table |

#### Call-site migration

In lockstep with each action landing:

- `ManageOrder.php`'s `getRefundAction()` / `getCaptureAction()` closures retired; header actions become a list of `…::make()` calls.
- `lunarphp/admin`'s `Support/Actions/Orders/UpdateStatusAction.php` and `UpdateStatusBulkAction.php` deleted; `OrderResource` and `OrderTable` reference the bridge classes directly. The duplicated bridge copy under `packages/filament/src/Tables/Actions/Orders/UpdateStatusBulkAction.php` is removed (the new home is `packages/filament/src/Actions/Orders/UpdateOrderStatusBulkAction.php`).
- `lunarphp/admin`'s `Support/Actions/Collections/*` deleted; the bridge's `CollectionTreeView` already uses the bridge copies, so this is a one-side delete plus call-site fixup wherever the admin copies are referenced.
- `lunarphp/admin`'s `Support/Actions/PdfDownload.php` moves into `Lunar\Filament\Actions\Orders\DownloadOrderPdfAction` (or stays as a generic `DownloadPdfAction` under `Lunar\Filament\Actions\Support\` — see Open questions).
- The unused stale tree at `packages/admin/src/Support/packages/filament/…` (empty directories from a previous reshape) is deleted as cleanup.

Each action lands with its call-site rewrite, its translation keys, and its test. No action is accepted without retiring at least one inline closure or duplicate class.

#### Naming convention

`{Verb}{Subject}Action` for single, `{Verb}{Subject}BulkAction` for bulk. `Subject` is the model name in singular for single actions, plural for bulk actions (`PublishProductsBulkAction`, not `PublishProductBulkAction`). Avoids the current mix of `MapVariantsToProductOptions` (no `Action` suffix) and `UpdateStatusBulkAction` (no model in name).

### Part B — Global search

#### `RecordSearch` is the single backend

The Scout-vs-translated-attribute logic in `HasScoutGlobalSearch` and the same logic in `RecordSearch::for()` collapse to one implementation under `Lunar\Filament\Forms\Components\Support\RecordSearch`. `HasScoutGlobalSearch` becomes a thin shim that delegates and is deprecated for removal in v3.

#### Per-model search descriptors

Each searchable Lunar model gets a static descriptor class under `Lunar\Filament\GlobalSearch\…`:

```
packages/filament/src/GlobalSearch/
    GlobalSearchDescriptor.php          // abstract base
    ProductGlobalSearch.php
    OrderGlobalSearch.php
    CustomerGlobalSearch.php
    CollectionGlobalSearch.php
    BrandGlobalSearch.php
```

Each descriptor exposes the same four methods Filament resources expect, lifted out of the resource into a shared class:

```php
abstract class GlobalSearchDescriptor
{
    abstract public static function getSearchableAttributes(): array;
    abstract public static function getResultDetails(Model $record): array;
    public static function getResultTitle(Model $record): string { /* default: translateAttribute('name') */ }
    public static function getEloquentQuery(): Builder { return static::getModel()::query(); }
}
```

The current `OrderResource::getGloballySearchableAttributes()` (lines 80-91), `getGlobalSearchResultDetails()` (lines 101-119), `getGlobalSearchEloquentQuery()` (lines 93-99), and `getGlobalSearchResultUrl()` (lines 73-78) all move into `OrderGlobalSearch`. Same shape for the other four resources.

#### `HasLunarGlobalSearch` consumer trait

A consumer wiring Lunar models into their own Filament resources gets one-line opt-in:

```php
namespace App\Filament\Resources;

use Filament\Resources\Resource;
use Lunar\Core\Models\Product;
use Lunar\Filament\GlobalSearch\Concerns\HasLunarGlobalSearch;
use Lunar\Filament\GlobalSearch\ProductGlobalSearch;

class MyProductResource extends Resource
{
    use HasLunarGlobalSearch;

    protected static ?string $model = Product::class;
    protected static string $globalSearch = ProductGlobalSearch::class;
}
```

The trait forwards `getGloballySearchableAttributes`, `getGlobalSearchResultDetails`, `getGlobalSearchResultTitle`, `getGlobalSearchEloquentQuery` to the named descriptor. `getGlobalSearchResultUrl` stays on the resource because only the resource knows its own URL — the descriptor doesn't.

`lunarphp/admin`'s `BaseResource` is rewritten to use this trait in place of `HasScoutGlobalSearch`, pointing at the bridge descriptors. The bridge descriptors are now the single source of truth; admin is one of multiple consumers.

#### URL resolver integration

The existing `RecordUrls` facade (introduced in spec 0006 for widgets) gains a parallel use here: a downstream consumer can register `RecordUrls::resolveUsing('product', …)` once, and the bridge can provide a *default* `HasLunarGlobalSearch::getGlobalSearchResultUrl()` that consults the resolver. Resources that want a custom URL keep overriding the method.

#### Plugin-style toggle

Global search registration is opt-in via the bridge's package config (spec 0006 already has `register_widgets_on_default_panel`; add `register_global_search_on_default_panel`). For panels using the full `lunarphp/admin` shell it stays on by default. For consumers using only `lunarphp/filament` it stays off unless they explicitly opt in.

### Cross-cutting decisions

#### Service-provider auto-registration

Both actions and global search descriptors are plain classes — no auto-registration needed for the actions (consumers wire them by calling `::make()`), and the descriptors are referenced by class name on the resource. The bridge service provider does not bind anything new globally for either feature.

#### Translation strategy

Two new lang files in `packages/filament/resources/lang/{locale}/`:

- `actions.php` — labels, modal headings, notification titles, confirmation copy for every action.
- `global-search.php` — result-details key labels (status, total, customer, email, date for orders; SKU, status, brand for products; etc.).

English first, mirrored across the other 15 locales (English value acceptable as a placeholder where no translation exists yet). The admin's existing `lunarpanel::order.action.refund_payment.*` keys are migrated to `lunar-filament::actions.orders.refund.*` and the admin keys are deprecated (Rector rule in `lunarphp/upgrade`).

#### Customisation strategy parity

The three customisation strategies documented in the README (extension hooks, subclass and rebind, publish stubs) apply identically to actions and descriptors:

- **Extension hooks** — `LunarFilament::extensions([RefundOrderAction::class => new class { public function configureAction($action) { ... } }])` for additive tweaks.
- **Subclass and rebind** — bind your subclass in the container; `::make()` resolves through the container so the swap propagates.
- **Publish stubs** — actions and descriptors are *not* publishable in v2 cycle. Both are runtime building blocks (like selectors) rather than consumer-owned templates. Revisit if downstream consumers ask.

#### PR slicing

One PR per logical group. Each PR introduces the new classes, retires the matching call sites, lands the translation keys, and adds tests. **Every group lands the core action(s) first, then the Filament wrapper that delegates to them** — there is no PR in which a Filament action contains business logic.

1. **Foundations** — `Lunar\Filament\Actions\Concerns\InteractsWithTransactions`, `ConfirmsDestructiveAction`; `Lunar\Filament\GlobalSearch\GlobalSearchDescriptor` base + `HasLunarGlobalSearch` trait; `actions.php` and `global-search.php` lang files (English only). `RecordSearch` consolidation (lift logic out of `HasScoutGlobalSearch`).
2. **Order actions** — core first: `Core\Actions\Orders\RefundOrder`, `CaptureOrder`, `UpdateOrderStatus`, `MarkOrderAsShipped` (with feature tests for each). Then the Filament wrappers: `RefundOrderAction`, `CaptureOrderAction`, `UpdateOrderStatusAction`, `UpdateOrderStatusBulkAction`, `MarkOrderAsShippedAction`, `MarkOrdersAsShippedBulkAction`, `AddOrderNoteAction` (Filament-only — single-field write), `DownloadOrderPdfAction` (Filament-only — rendering). Retires `ManageOrder.php` inline closures (including the `availableToRefund`/`canBeRefunded`/`charges` computed properties — they move into `RefundOrder`) and the duplicated `UpdateStatusBulkAction` copies.
3. **Product actions** — core first: `Core\Actions\Products\DuplicateProduct`, `UpdateProductStatus`, `AdjustStock`, and the lifted `MapVariantsToProductOptions`. Then the Filament wrappers: `DuplicateProductAction`, `PublishProductsBulkAction`, `UnpublishProductsBulkAction`, `ArchiveProductsBulkAction`, `AdjustStockAction`, `MapVariantsToProductOptionsAction`.
4. **Collection actions** — core first: lift `Admin\Support\Actions\Collections\*` into `Core\Actions\Collections\*` (the move logic in particular has nontrivial lft/rgt handling that should not be in admin). Then the Filament wrappers: `CreateRootCollectionAction`, `CreateChildCollectionAction`, `MoveCollectionAction`, `DeleteCollectionAction`. Retires the duplicated `Support/Actions/Collections/*` in admin and the stale empty trees.
5. **Global search descriptors** — `OrderGlobalSearch`, `ProductGlobalSearch`, `CustomerGlobalSearch`, `CollectionGlobalSearch`, `BrandGlobalSearch`. Migrates the five admin resources to `HasLunarGlobalSearch`. Deprecates `HasScoutGlobalSearch`. No business logic here — global search is purely presentational.
6. **Translations + cleanup** — mirror lang files across the other 15 locales, delete stale `packages/admin/src/Support/packages/filament/…` directories, regenerate translations, run `vendor/bin/pint --dirty`.

## Alternatives considered

- **Ship actions only; defer global search to a later spec.** Rejected — they share the `RecordSearch` backend and the same "bridge should ship verbs and discovery, not just nouns" story. Splitting doubles the PR overhead and the global-search work is small enough not to need its own spec.
- **Ship a single `Lunar::action('refund')` registry instead of one class per action.** Rejected for the same reason a `LunarSelect::for(Product::class)` was rejected in spec 0008 — entity-specific fluent options, IDE autocomplete, and subclass-and-rebind customisation all want concrete classes. A registry hides what's actually available.
- **Leave the actions inline and only ship the global search descriptors.** Rejected — actions are the more frequently-asked-for piece (refund and capture come up in every consumer build), and leaving them inline guarantees the next consumer copies the closure out and maintains a stale version. Discovery without verbs is a half answer.
- **Build a generic "state machine action" that drives every status change.** Rejected for v2 — pairs with the state-machines TODO item which isn't done yet. Once state machines land, the per-action status verbs (`MarkOrderAsShippedAction` etc.) collapse into transitions on the machine; document that follow-up but don't block this spec on it.
- **Register actions/descriptors automatically with downstream resources.** Rejected — the bridge does not own the consumer's resource class. The consumer wires actions explicitly (idiomatic Filament) and opts into global search via the trait + `$globalSearch = …Descriptor::class` line.

## Migration impact

- **Database**: none.
- **Public contract surface**: net additive.
    - New public classes under `Lunar\Core\Actions\Orders\…`, `Lunar\Core\Actions\Products\…`, `Lunar\Core\Actions\Collections\…` — these are the long-lived contract that downstream consumers (UI, API, CLI, future state machines) build on. The Filament classes are wrappers.
    - New public classes under `Lunar\Filament\Actions\…` and `Lunar\Filament\GlobalSearch\…`.
    - `Lunar\Admin\Support\Resources\Concerns\HasScoutGlobalSearch` is deprecated and becomes a thin proxy to the new global-search backend; remove in v3.
    - `Lunar\Admin\Support\Actions\Orders\UpdateStatusAction`, `UpdateStatusBulkAction`, `Collections\MoveCollection`, `Collections\CreateChildCollection`, `Collections\CreateRootCollection`, `Collections\DeleteCollection`, `PdfDownload` are deprecated; thin proxies forward to the bridge classes for one minor cycle, then removed.
    - `Lunar\Filament\Tables\Actions\Orders\UpdateStatusBulkAction` and `Lunar\Filament\Tables\Actions\Collections\CreateChildCollection` are removed and their tables updated to reference `Lunar\Filament\Actions\…` directly. These are bridge-package surface that no consumer documentation has pointed at yet, so removal is acceptable without a deprecation cycle.
- **Upgrade path for v1.x consumers**: there are no v1 first-class action classes to migrate. The Rector rule in `lunarphp/upgrade` rewrites references to the deprecated admin action classes onto the bridge equivalents, and rewrites the deprecated `lunarpanel::order.action.*` translation keys onto `lunar-filament::actions.*` ones.
- **Translation impact**: two new lang files (`actions.php`, `global-search.php`) in `packages/filament/resources/lang/`, 16 locales. Existing `lunarpanel::order.action.*` keys keep working for the deprecation cycle.
- **Filament / admin impact**: the in-repo migration of the `ManageOrder` header actions, the `OrderTable` bulk action, the five admin resources with `getGlobalSearch*` methods, and the deleted duplicate classes. No end-user behaviour change beyond consistency fixes (every action now has a translated label, every bulk action confirms the same way, every global search uses the same backend).

## Resolved

The following design questions were settled during spec review and are recorded here so the rationale travels with the spec:

- **Result-object shape for core actions.** Each core action returns whatever shape is natural for the operation — a record, a boolean, a value object, `void`. No forced `ActionResult` envelope. The Filament action knows what its core counterpart returns and translates that into a notification. Rationale: a uniform envelope would add a new core public class to cover variance that doesn't currently exist; the existing `$transaction->refund()` shape sets the precedent for "result object only where the operation genuinely has multiple outcomes".
- **Not every Filament action needs a core counterpart.** A core action is warranted only when there's domain validation, multi-step / cross-aggregate state change, side effects worth dispatching from anywhere, or arithmetic the model doesn't expose. Purely presentational actions (PDF download), single-field writes the model already covers (add order note), and Filament-only UX affordances stay in Filament. See the "boundary" section above for the full criteria.
- **State-machine coupling for `UpdateOrderStatus` / `UpdateProductStatus`.** Ship flat status setters now (`UpdateOrderStatus::run($order, 'shipped')`). When the state-machines TODO item lands, re-implement the internals as transitions without changing the public signature. Rationale: designing the transition interface before the state-machine subsystem exists is premature; the name and signature survive a state-machine re-implementation because only the body changes.
- **Bulk action confirmation defaults.** Always-confirm for destructive bulk actions (unpublish, archive, delete). Never-confirm for non-destructive (publish, mark shipped). `->withoutConfirmation()` is the opt-out.

## Open questions

- **PDF download generalisation.** `PdfDownload` in admin is generic (`->pdfView(...)`) — does the bridge ship it as `Lunar\Filament\Actions\Support\DownloadPdfAction` for reuse beyond orders, or as `Lunar\Filament\Actions\Orders\DownloadOrderPdfAction` with the order view pre-wired? Recommend: ship the generic one *and* a pre-wired order subclass; admin uses the order subclass. Tactical, decide in PR.
- **Stock action scope.** `AdjustStockAction` is a single-line +/- with a reason. Lunar's full Inventory work is on the Ideas list (TODO.md). Does the v2 action ship now and get superseded later, or wait for Inventory? Recommend: ship now against the existing `stock`/`backorder` columns; supersede when Inventory lands.
- **Where the Concerns live.** `InteractsWithTransactions` could be a trait or a base abstract action. Recommend: trait, because Refund and Capture share *some* of the schema (transaction picker, amount, notes) but not all of it (refund has "available to refund" maths, capture has "remaining to capture" maths); composition stays cleaner than an inheritance ladder. Tactical, decide in PR.
- **Should the bridge ship a generic `LunarPlugin::make()` install path now, as part of this spec, instead of the per-config opt-in toggles?** A `LunarPlugin::make()->actions()->globalSearch()->widgets()` plugin object is on the broader Filament ideas list (`packages/filament/IDEAS.md` #1). Recommend: keep that as its own spec — this one is large enough, and the per-config toggles introduced here become natural inputs to that plugin object later.

## References

- [[0006-filament-bridge-package]] (implemented) — established the bridge package this spec extends.
- [[0008-filament-entity-selectors]] (implemented) — established the `RecordSearch` service this spec consolidates onto, and the call-site-migration-in-lockstep model this spec follows.
- `packages/filament/IDEAS.md` — items #2 (Filament Actions library) and #4 (Global Search providers) are the source of this spec.
- `packages/lunar/packages/core/src/Actions/AbstractAction.php` — base class every new `Lunar\Core\Actions\…` class extends; `make()`/`run()`/`then()` convention this spec follows.
- `packages/lunar/packages/core/src/Models/Transaction.php:96-101` — existing `refund()` and `capture()` methods that `Core\Actions\Orders\RefundOrder` and `CaptureOrder` delegate to.
- `packages/lunar/packages/admin/src/Filament/Resources/OrderResource/Pages/ManageOrder.php:380` — current inline `getRefundAction()` closure to be replaced by `RefundOrderAction` (UI) + `Core\Actions\Orders\RefundOrder` (business logic, including the `availableToRefund`, `canBeRefunded`, and `charges` properties currently on the page).
- `packages/lunar/packages/admin/src/Support/Actions/Orders/UpdateStatusAction.php` and `UpdateStatusBulkAction.php` — current admin-package action classes to be lifted into the bridge.
- `packages/lunar/packages/filament/src/Tables/Actions/Orders/UpdateStatusBulkAction.php` — the drifted bridge-package copy to be consolidated.
- `packages/lunar/packages/admin/src/Support/Resources/Concerns/HasScoutGlobalSearch.php` — current Scout-aware backend to collapse onto `Lunar\Filament\Forms\Components\Support\RecordSearch`.
- `packages/lunar/packages/admin/src/Filament/Resources/{Product,Order,Customer,Collection,Brand}Resource.php` — current `getGloballySearchableAttributes` / `getGlobalSearchResultDetails` / `getGlobalSearchEloquentQuery` definitions to lift into descriptor classes.
- `packages/lunar/packages/admin/src/Support/Actions/Collections/*` and `packages/lunar/packages/filament/src/Actions/Collections/*` — duplicated collection actions to consolidate in the bridge.
