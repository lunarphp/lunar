# Lunar PR Review — Full Rubric

Load this on demand from `SKILL.md` when a category needs deeper rationale or examples. Every example is anchored to a real file in this repo so the reviewer can verify the pattern.

---

## Translations

**Locales tracked (15 peer + en)**: `ar, bg, de, en, es, fa, fr, hr, hu, mn, nl, pl, pt_BR, ro, tr, vi`.

**Source of truth**: `packages/*/resources/lang/en/*.php`.

**Format**: nested associative arrays — keys are flattened to dot notation (`form.handle.label`).

Example (`packages/admin/resources/lang/en/channel.php`):

```php
return [
    'label' => 'Channel',
    'plural_label' => 'Channels',
    'table' => [
        'name' => ['label' => 'Name'],
    ],
];
```

Used in code as `__('lunarpanel::channel.label')` (see `packages/admin/src/Filament/Resources/ChannelResource.php:38`).

**Special case**: `packages/admin/resources/lang/*/discount.php` references `Lunar\Models\Discount`. The diff script boots Composer autoload so this loads. If a peer locale is missing the `use` statement or has stale class references, the file load will fail — report that as a Blocker, not a missing-key.

**Hardcoded strings to flag**:
- `Filament\…\TextInput::make('foo')->label('Some Label')` → must be `->label(__('lunarpanel::foo.bar'))`.
- Notification titles/bodies, action labels, table column headers, navigation group/sort names.

---

## Tests (Pest)

**Test roots**: `tests/core`, `tests/admin`, `tests/opayo`, `tests/paypal`, `tests/shipping`, `tests/stripe`, `tests/search` (mapped in `composer.json` autoload-dev).

**Factories**: `packages/core/database/factories/*Factory.php` (e.g. `CustomerGroupFactory`, `OrderLineFactory`).

**Expected coverage for new code**:
| New file pattern                                   | Expected test path                              | Factory? |
|----------------------------------------------------|-------------------------------------------------|----------|
| `packages/core/src/Models/Foo.php` (Eloquent)      | `tests/core/Unit/Models/FooTest.php`            | Yes      |
| `packages/core/src/Actions/.../FooAction.php`      | `tests/core/Unit/Actions/.../FooActionTest.php` | n/a      |
| `packages/core/src/Observers/FooObserver.php`      | `tests/core/Feature/Observers/FooObserverTest.php` | n/a   |
| `packages/admin/src/Filament/Resources/FooResource.php` | `tests/admin/Feature/Filament/FooResourceTest.php` | n/a |

Create with `php artisan make:test --pest <Name>Test` (Pest is the test framework — `pestphp/pest` in `composer.json`).

---

## Migrations

**Base class**: `Lunar\Base\Migration` (`packages/core/src/Base/Migration.php`).
- Sets `$this->prefix` from `config('lunar.database.table_prefix')` in the constructor.
- Provides `getConnection()` honoring `config('lunar.database.connection')`.
- Anonymous class pattern: `return new class extends Migration { … };`.

**Reference template** (`packages/core/database/migrations/2025_11_12_005200_add_meta_to_product_options.php`):

```php
return new class extends Migration
{
    public function up(): void
    {
        Schema::table($this->prefix.'product_options', function (Blueprint $table) {
            $table->jsonb('meta')->nullable()->after('shared');
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix.'product_options', function (Blueprint $table) {
            $table->dropColumn('meta');
        });
    }
};
```

**Blockers**:
- Missing `down()`, or `down()` that does not reverse `up()`.
- Hardcoded table name (`'lunar_orders'` instead of `$this->prefix.'orders'`).
- Extending the wrong base class.
- Destructive column drops on tables that may contain customer data with no explicit data-loss note.

**Should fix**:
- Foreign keys without `constrained()` or without explicit `onDelete` behavior.
- Forgetting `->after(...)` on column adds when ordering matters.
- Adding non-nullable columns to populated tables without a default or a paired backfill.

---

## Filament resources & admin

**Reference**: `packages/admin/src/Filament/Resources/ChannelResource.php`.

Required surface for every resource:

```php
class FooResource extends BaseResource
{
    protected static ?string $permission = 'settings:core';
    protected static ?string $model = FooContract::class; // Lunar\Models\Contracts\Foo
    protected static ?int $navigationSort = 1;

    public static function getLabel(): string         { return __('lunarpanel::foo.label'); }
    public static function getPluralLabel(): string   { return __('lunarpanel::foo.plural_label'); }
    public static function getNavigationIcon(): ?string { return FilamentIcon::resolve('lunar::foos'); }
    public static function getNavigationGroup(): ?string { return __('lunarpanel::global.sections.settings'); }
}
```

**Blockers**:
- `$model` referencing a concrete class (`Channel::class`) instead of `ChannelContract::class`. This breaks consumer overrides via the model manifest.

**Should fix**:
- Missing `$permission`.
- Hardcoded English in labels, helper text, validation messages, action confirmations.
- Bypassing `BaseResource` (extend it, don't reach for `Filament\Resources\Resource` directly).

---

## Model contracts (type-hinting)

**Why**: `packages/core/src/Base/ModelManifest.php` binds each `Lunar\Models\Contracts\*` interface to its concrete model in the container. Consumers replace a model with `Lunar::useModel(ProductContract::class, MyProduct::class)`. Code that type-hints the concrete `Lunar\Models\Product` silently bypasses that override — the consumer's subclass still gets stored, but a `Product $product` parameter rejects it on a strict type check, and IDE/static-analysis assumes properties that the subclass may have changed.

**Where the contract is required** (non-exhaustive — apply to all changed code):

| Surface                                | Example                                                                              |
|----------------------------------------|--------------------------------------------------------------------------------------|
| Pipeline `handle()` params/returns     | `public function handle(OrderContract $order, Closure $next): mixed`                 |
| Manager methods                        | `public function apply(CartContract $cart): CartContract`                            |
| Promoted constructor properties        | `public function __construct(public ?CartContract $cart = null) {}`                  |
| Action / job / listener method params  | `public function handle(ProductContract $product): void`                             |
| Event constructor params & properties  | `public function __construct(public OrderContract $order) {}` (also: breaking-change risk) |
| PHPDoc `@param` / `@return` / `@var`   | `@return \Illuminate\Support\Collection<int, ProductContract>`                       |
| Container resolution                   | `app(ProductContract::class)`, `resolve(CartContract::class)`                        |
| Filament resource `$model`             | `protected static ?string $model = ProductContract::class;`                          |

**Reference patterns from the codebase**:

- `packages/core/src/Pipelines/Order/Creation/CreateOrderLines.php` — `handle(OrderContract $order, Closure $next)`.
- `packages/core/src/Managers/CartSessionManager.php` — `public ?CartContract $cart = null` and `use(CartContract $cart): CartContract`.
- `packages/core/src/PaymentTypes/AbstractPayment.php` — `protected ?CartContract $cart = null; protected ?OrderContract $order = null;`.

**Where concrete classes are correct** (do not flag):

- The model class itself, plus its relations, scopes, casts, observers' `$model` property.
- `database/factories/*Factory.php` and `database/seeders/`.
- Migrations.
- Test fixtures: `Product::factory()->create()`, `Order::find(1)`.
- Type-narrowing `instanceof` checks with a documented reason.

**Common smells to flag**:

```php
// Bad — defeats consumer overrides
use Lunar\Models\Cart;

public function handle(Cart $cart, Closure $next): mixed { … }

// Good
use Lunar\Models\Contracts\Cart as CartContract;

public function handle(CartContract $cart, Closure $next): mixed { … }
```

```php
// Bad
public function __construct(public Order $order) {}

// Good
public function __construct(public OrderContract $order) {}
```

```php
// Bad
$cart = app(Cart::class);
$cart = new Cart;

// Good
$cart = app(CartContract::class);
```

**Heuristic for the reviewer**: in the diff, for each `use Lunar\Models\<Name>;` added outside an exempt path, grep the same file for `<Name> $` (parameter/property), `: <Name>` (return type), `@param <Name>`, `@return <Name>`, `new <Name>`, and `app(<Name>::class)`. Each hit is a Blocker finding unless covered by an exemption above.

**Severity**: Blocker — the public contract surface is part of the 1.x stability promise, and this directly affects model-extension consumers. Pair this finding with a fix snippet so it's a one-line rename rather than a discussion.

---

## PHP conventions (from `CLAUDE.md`)

| Rule                                    | Example                                                                       |
|-----------------------------------------|-------------------------------------------------------------------------------|
| Constructor property promotion          | `public function __construct(public GitHub $github) {}`                       |
| Typed everything                        | `function isAccessible(User $user, ?string $path = null): bool`              |
| Curly braces always                     | `if ($x) { return; }` not `if ($x) return;`                                   |
| Enum case keys in TitleCase             | `FavoritePerson`, `Monthly`                                                   |
| PHPDoc with array shapes                | `@return array{id: int, name: string}`                                        |
| No `env()` outside config               | use `config('lunar.foo')` in app code                                         |

---

## Static analysis & style

- `phpstan.neon.dist`: level 0, excludes `tests/`, `config/`, `vendor/`. Treat findings outside excluded paths as Should-fix.
- `pint.json`: Laravel preset only. Always remind: `vendor/bin/pint --dirty --format agent`.
- Don't run `vendor/bin/pint --test` — per `CLAUDE.md`, fix-and-format is the workflow.

---

## Composer / dependencies

`CLAUDE.md` says: "Do not change the application's dependencies without approval."

Any of these is a Blocker until the user confirms:
- `require` additions/removals in root `composer.json`.
- `require` additions/removals in any `packages/*/composer.json`.
- PHP/Laravel/Filament version-constraint bumps.
- New autoload entries that aren't tied to a new package.

`composer.lock` changes alone are fine if `composer.json` is untouched.

---

## Public API surface (1.x stability)

The `1.x` branch is stable — these changes are breaking and need a major bump or a deprecation path:

- Anything in `packages/*/src/Models/Contracts/*.php`: added abstract methods, removed methods, renamed methods, changed parameter or return types.
- Public method signature changes on classes implementing a `Contracts/*` interface.
- Removed/renamed events.
- Removed config keys in `packages/*/config/*.php` (renaming requires alias + deprecation).
- Renamed table/column names without an accessor preserving the old field.

Flag with explicit "**Breaking change on 1.x**" prefix so it can't be missed.

---

## Performance & data-integrity (medium-value)

### N+1 in admin tables and resources

- Filament `TextColumn::make('relation.field')` triggers a query per row unless `getEloquentQuery()` eager-loads.
- Look for added relation accessors in `Resources/*Resource.php` without a matching `with(...)` on `getEloquentQuery`.
- Suggestion: `protected function getEloquentQuery(): Builder { return parent::getEloquentQuery()->with(['…']); }`

### Indexes on new columns

Any column referenced by `where`, `orderBy`, or used as a foreign key in queries should have an index in the same migration:

```php
$table->string('status')->index();
$table->index(['channel_id', 'status']);   // composite for common filters
```

`foreignId()->constrained()` adds an index automatically; bare `unsignedBigInteger('foo_id')` does not.

### Transactions on multi-step writes

Lunar's order/cart paths persist several related rows. Wrap in `DB::transaction()`:

```php
DB::transaction(function () use ($payload) {
    $order = Order::create($payload['order']);
    $order->lines()->createMany($payload['lines']);
    $order->addresses()->createMany($payload['addresses']);
});
```

Flag new multi-write actions/observers/listeners that mutate >1 table without a transaction.

### Soft-delete awareness

Many Lunar models use `SoftDeletes` (`Product`, `Customer`, `Order` family, …):

- New queries that should include trashed need `withTrashed()` explicitly; default scope hides them.
- Unique indexes on columns of soft-deletable models can collide with trashed rows — usually want partial indexes or a tombstone column.
- New `delete()` calls should consider whether `forceDelete()` is what the caller actually wants.

### Authorization beyond Filament

`$permission` on Filament resources covers the panel — not custom Livewire components, controllers, jobs, or commands.

- New routes touching customer/order/payment data should declare middleware or `Gate::authorize(...)`.
- New jobs that act on a customer's behalf should accept the actor and re-check permissions, not trust the caller.

---

## Also consider (opt-in for relevant PRs)

These are lower-frequency but worth flagging when the diff touches the relevant area:

- **Activity log** — `spatie/laravel-activitylog` is loaded. New `Order`/`Customer`/`Discount` writes that bypass `LogsActivity` lose the audit trail.
- **Cache invalidation** — `spatie/laravel-blink` is per-request; long-lived caches (taxonomy, structure) need explicit busting on write.
- **Mass-assignment surface** — additions to `$fillable` should not include foreign keys to ownership boundaries (`customer_id`, `user_id`) on publicly-exposed models without explicit guarding upstream.
- **Validation** — new admin/API endpoints should use FormRequest classes, not inline `request()->validate(...)`.
- **Magic strings → enums** — order/transaction/payment status literals should reference the existing enum cases; new string-literal comparisons are smell.
- **Payment-provider PRs** (`packages/stripe`, `packages/paypal`, `packages/opayo`) — verify webhook signature checks, idempotency keys, and that card data never reaches logs/exceptions.
- **Notifications & mail** — new notification subjects/bodies and `resources/views/vendor/mail` templates also need translating, not just lang files.
- **User-facing copy** — spelling/grammar on labels, error messages, and notification text. Cheap to fix in review, expensive after release.
- **Filament navigation** — added resources without `$navigationSort` slot to the bottom of the group; usually fine, but flag if it disrupts grouping.

---

## Reuse existing scopes & traits

Lunar models lean heavily on traits in `packages/core/src/Base/Traits/`. New code that reaches into related tables with hand-rolled `where` chains usually has a scope sitting right there waiting to be used.

**Traits to check first**:

| Trait                                       | Use instead of…                                                            |
|---------------------------------------------|-----------------------------------------------------------------------------|
| `HasCustomerGroups`                         | `->customerGroups()->where('enabled', true)->where(fn($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))…` ladders |
| `HasChannels`                               | `->channels()->where('enabled', true)…` — use `->channel($channel)` instead |
| `HasUrls`                                   | manual `->urls()->where('default', true)->first()`                          |
| `HasTags` / `HasMedia` / `HasTranslations`  | bespoke pivot filtering — check the trait first                             |

**Bad** (`packages/admin/src/Filament/Resources/ProductResource.php` — flagged on PR #2468):

```php
return $record->customerGroups()
    ->where('customer_group_id', $default->id)
    ->where(fn ($q) => $q->where('enabled', true)->orWhere('visible', true))
    ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
    ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
    ->exists();
```

**Better** — let the trait's scope encode the eligibility rules so they stay consistent across resources, validators, and pipelines:

```php
return $record->customerGroups()
    ->whereKey($default->id)
    ->active() // or whichever scope HasCustomerGroups exposes for this window
    ->exists();
```

When flagging, point at the trait file so the user can verify the scope name — e.g. `packages/core/src/Base/Traits/HasCustomerGroups.php`. If the scope genuinely doesn't exist yet but the same query is repeated, suggest adding it.

**Validators / pipeline stages**: `packages/core/src/Validation/**` and `packages/core/src/Pipelines/**` are particularly prone to inlining trait logic. A validator that grows past ~30 lines of query composition should either extract to a model scope or to a small invokable rule class.

---

## Control-flow simplification

Reviewers actively flag stacked `if` guards in this repo (PR #2468 had 5 separate comments on the same file). The pattern shows up most in `Shout::make(...)->hidden(fn (Model $record) => …)` closures on Filament resources, and in cart/order validators.

**Bad** — back-to-back guards that all return the same literal:

```php
->hidden(function (Model $record) {
    if ($record?->status != 'published') {
        return true;
    }

    return $record->customerGroups()->where('enabled', true)->count();
});
```

**Better** — fold the guard into the return:

```php
->hidden(fn (Model $record) =>
    $record?->status != 'published'
    || $record->customerGroups()->enabled()->exists()
);
```

**Bad** — three guards, one expression:

```php
if ($record?->status != 'published') { return true; }
if (! $record->customerGroups()->where('enabled', true)->exists()) { return true; }
$default = CustomerGroup::getDefault();
if (! $default) { return true; }

return $record->customerGroups()->…->exists();
```

**Better** — early-return *only* when the next computation can't run; collapse same-outcome guards:

```php
if ($record?->status != 'published'
    || ! $record->customerGroups()->enabled()->exists()
) {
    return true;
}

$default = CustomerGroup::getDefault();

return ! $default || $record->customerGroups()->eligibleFor($default)->exists();
```

**Severity calibration**:
- One stacked closure → Nit.
- 3+ stacked guards in one closure, or the same pattern across sibling components in one file → Should-fix.
- Closure exceeds ~5 statements of query composition → Should-fix; recommend extracting to a private method or a model scope.

`exists()` vs `count()`: while reviewing, also flag `->count()` used purely as a boolean (the original `ProductResource.php` example does this) — `->exists()` is faster and clearer.

Keep curly braces (per repo PHP conventions) — *simplifying* doesn't mean stripping braces; it means removing the guards entirely or merging them.

---

## Severity quick guide

- **Blocker**: would break consumers, leak data, or land an obvious bug. Don't merge without fixing.
- **Should fix**: technically mergeable but degrades the codebase — missing tests, hardcoded strings in admin, stale translations.
- **Nit**: style, naming, redundant comment, reminder to run formatters.
