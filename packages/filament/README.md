# Lunar Filament Bridge

Filament v5 components, widgets, schemas, and tables for Lunar — the e-commerce primitives behind the Lunar admin panel, packaged so you can drop them into any Filament v5 panel.

This package ships the reusable building blocks: a product picker that knows about Lunar's translated names and Scout-aware search, a translated-text input that round-trips your locale data, dashboard chart widgets pre-wired against the order schema, a complete set of resource forms and tables for every commerce model, and the attribute system that powers Lunar's flexible product data.

If you want Lunar's complete turnkey admin panel — navigation, branding, auth, dashboard — install [`lunarphp/admin`](https://packagist.org/packages/lunarphp/admin) instead. It depends on this package.

## Requirements

- PHP 8.3+
- Laravel 11+ (Lunar v2 supports Laravel 13)
- Filament v5
- `lunarphp/core` (installed automatically)

## Installation

```bash
composer require lunarphp/core lunarphp/filament
```

The service provider auto-registers via Laravel's package discovery. No further wiring is required to start using the form components, table columns, infolist entries, or selectors.

To run Lunar's database migrations against your app:

```bash
php artisan migrate
```

## What's in the box

| Surface | Namespace | Examples |
| --- | --- | --- |
| Entity selectors | `Lunar\Filament\Forms\Components\*Select` | `ProductSelect`, `CollectionSelect`, `CurrencySelect` |
| Generic form components | `Lunar\Filament\Forms\Components\*` | `Attributes`, `TranslatedText`, `MediaSelect`, `Tags`, `Vimeo`, `YouTube` |
| Table columns | `Lunar\Filament\Tables\Columns\*` | `TranslatedTextColumn`, `ThumbnailImageColumn` |
| Infolist entries | `Lunar\Filament\Infolists\Components\*` | `Timeline`, `Tags`, `Transaction` |
| Resource schemas | `Lunar\Filament\Schemas\{Model}\{Model}Form` | `ProductForm`, `OrderForm`, `BrandForm`, `LocationForm`, `RegionForm`, … (22 models) |
| Resource tables | `Lunar\Filament\Tables\{Model}\{Model}Table` | `ProductTable`, `OrderTable`, … |
| Relation managers | `Lunar\Filament\RelationManagers\{Model}\*` | Customer addresses, Discount conditions, ProductOption values, … |
| Actions | `Lunar\Filament\Actions\{Subject}\*Action` | `RefundOrderAction`, `CaptureOrderAction`, `DuplicateProductAction`, `PublishProductsBulkAction` |
| Global-search descriptors | `Lunar\Filament\GlobalSearch\*GlobalSearch` | `OrderGlobalSearch`, `ProductGlobalSearch`, `CustomerGlobalSearch` |
| Dashboard widgets | `Lunar\Filament\Widgets\*` | `OrderStatsOverview`, `OrdersSalesChart`, `LatestOrdersTable` |
| Attribute system | `Lunar\Filament\FieldTypes\*` | `TextField`, `TranslatedText`, `Dropdown`, `Toggle`, `ListField`, … |

---

## Entity selectors

Sixteen first-class `Select` (and one `MorphToSelect`) subclasses for picking Lunar records by relationship. They all share one Scout-aware search backend, default to translated names where applicable, and ship with sensible Lunar-flavoured defaults.

| Selector | Picks | Notable extras |
| --- | --- | --- |
| `ProductSelect` | Product | `->showSku()`, `->scopeStatus('published')`, `->withinChannel($channel)`, `->excludeAttached()` |
| `ProductVariantSelect` | ProductVariant | `->forProduct($product)`, `->searchViaProduct()`, label is `"Product — SKU"` |
| `CollectionSelect` | Collection | breadcrumb path label, `->excludeDescendantsOf($collection)`, `->excludeSelf($collection)`, `->withinGroup($group)` |
| `BrandSelect` | Brand | relationship-bound to `brand`, inline create form |
| `ProductTypeSelect` | ProductType | preloaded, relationship-bound |
| `TagSelect` | Tag | multi-select by default |
| `CustomerSelect` | Customer | multi-field search across `first_name`, `last_name`, `company_name` |
| `CustomerGroupSelect` | CustomerGroup | preloaded, relationship-bound to `customerGroup` |
| `DiscountTargetSelect` | Polymorphic Product/Variant/Collection/Brand | `->targets([Product::class, Collection::class])` per call-site |
| `CurrencySelect` | Currency | defaults to `Currency::getDefault()`, preloaded |
| `ChannelSelect` | Channel | preloaded |
| `LanguageSelect` | Language | preloaded |
| `TaxClassSelect` | TaxClass | defaults to `TaxClass::getDefault()`, preloaded |
| `TaxZoneSelect` | TaxZone | preloaded |
| `CountrySelect` | Country | emoji + native-name label, `->iso3()` mode |
| `StateSelect` | State | datalist-backed, `->dependsOn('country_id')` |

### Basic usage

Drop a selector into any Filament form, schema, or action — it works exactly like a `Select`:

```php
use Lunar\Filament\Forms\Components\ProductSelect;

ProductSelect::make('product_id')
    ->required();
```

### Searching, filtering, and de-duplication

```php
use Lunar\Core\Models\Channel;
use Lunar\Filament\Forms\Components\ProductSelect;

ProductSelect::make('product_id')
    ->showSku()                                  // append " — {sku}" to each result
    ->scopeStatus('published')                   // only published products
    ->withinChannel(Channel::getDefault())       // only products attached to this channel
    ->excludeAttached()                          // hide records already on the surrounding relation
    ->multiple();                                // multi-select is supported on every selector
```

`->excludeAttached()` is a no-op outside a `RelationManager` context, and inside one it dedupes against the `getRelationship()->get()` ids automatically — including for `AttachAction`-driven attach modals.

### Variants

```php
use Lunar\Filament\Forms\Components\ProductVariantSelect;

// Direct variant search (SKU + product name)
ProductVariantSelect::make('variant_id');

// Search products first, then list every variant of the matched product
ProductVariantSelect::make('variant_id')->searchViaProduct();

// Restrict to one product (e.g. a row already chose the parent)
ProductVariantSelect::make('variant_id')->forProduct($product);
```

### Collections

```php
use Lunar\Filament\Forms\Components\CollectionSelect;

CollectionSelect::make('collection_id')
    ->excludeDescendantsOf($currentCollection)   // safe re-parent target picker
    ->excludeSelf($currentCollection);
```

The default label format is the breadcrumb path: `"Men > Outerwear > Jackets"`.

### Country & State (dependent datalist)

```php
use Lunar\Filament\Forms\Components\CountrySelect;
use Lunar\Filament\Forms\Components\StateSelect;

CountrySelect::make('country_id')->live();
StateSelect::make('state')->dependsOn('country_id');
```

`CountrySelect::iso3()` switches the field to store ISO3 codes instead of foreign keys — useful for `TaxZone` countries and other ISO-keyed columns.

### Polymorphic discount targets

```php
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Filament\Forms\Components\DiscountTargetSelect;

DiscountTargetSelect::make('discountable')
    ->targets([Product::class, ProductVariant::class, Collection::class, Brand::class]);
```

### Using a selector inside `AttachAction`

Filament's `AttachAction::recordSelect(fn ($select) => …)` callback hands you the existing `Select` rather than letting you swap in a subclass. Lunar selectors expose a static `applyTo($select)` helper for exactly this case:

```php
use Filament\Actions\AttachAction;
use Filament\Forms\Components\Select;
use Lunar\Filament\Forms\Components\CollectionSelect;

AttachAction::make()
    ->recordSelect(fn (Select $select) => CollectionSelect::applyTo($select));
```

### Picking your own search backend

If you need to bend the search query for one call-site, the `modifyOptionsQueryUsing()` hook is stackable:

```php
ProductSelect::make('product_id')
    ->modifyOptionsQueryUsing(fn ($query) => $query->whereHas('media'));
```

The underlying search service is `Lunar\Filament\Forms\Components\Support\RecordSearch` — call it directly from your own components:

```php
use Lunar\Core\Models\Product;
use Lunar\Filament\Forms\Components\Support\RecordSearch;

$results = RecordSearch::for(Product::class, $search)->take(20)->get();
```

It prefers Laravel Scout when both `lunar.panel.scout_enabled` is true and the model uses Scout's `Searchable` trait, falls back to a translated-attribute DB search, and falls back again to a plain `name` column search for models that have neither.

---

## Other form components

### `Attributes` — the Lunar attribute editor

Renders editable fields for every attribute attached to the current model (Product, Brand, Collection, etc.) according to its registered field type. Each field type knows how to draw itself, cast its data, and synthesize across Livewire.

```php
use Lunar\Filament\Forms\Components\Attributes;

Attributes::make();                                          // attributes for the resource model
Attributes::make()->using(ProductVariant::class);            // attributes for a different model
Attributes::make()->relationship('variant');                 // load/save through a relationship
```

### `TranslatedText` — locale-aware text input

```php
use Lunar\Filament\Forms\Components\TranslatedText as TranslatedTextInput;

TranslatedTextInput::make('name')->required();
```

Renders one input per Lunar `Language`, hydrating from `attribute_data->name->value` (translated `Text` and `TranslatedText` field types). Pair with `TranslatedRichEditor` for HTML content.

### `MediaSelect`, `Tags`, `Vimeo`, `YouTube`

Spatie Media Library integration, a chip-style tag input with autocomplete suggestions, and embedded-video field types for common content needs.

### `AttributeSelector`, `PermissionSelector`

CheckboxList-based pickers for product type → attribute mapping and staff → permission assignment respectively.

---

## Table columns

### `TranslatedTextColumn`

Renders a translated attribute, with optional tooltip when truncated:

```php
use Lunar\Filament\Tables\Columns\TranslatedTextColumn;

TranslatedTextColumn::make('attribute_data.name')
    ->attributeData()
    ->limit(40)
    ->limitedTooltip();
```

### `ThumbnailImageColumn`

Renders a square thumbnail resolved from a closure:

```php
use Lunar\Filament\Tables\Columns\ThumbnailImageColumn;

ThumbnailImageColumn::make('thumbnail')
    ->resolveThumbnailUrlUsing(fn ($record) => $record->getThumbnailImage());
```

---

## Infolist entries

- `Lunar\Filament\Infolists\Components\Timeline` — activity-log timeline (Spatie Activitylog backed).
- `Lunar\Filament\Infolists\Components\Tags` — read-only tag chips.
- `Lunar\Filament\Infolists\Components\Transaction` — payment-transaction summary card.

---

## Resource schemas, tables, and relation managers

Each Lunar commerce model has a complete Filament schema set under `Lunar\Filament\Schemas\{Model}` and `Lunar\Filament\Tables\{Model}`. They are the same classes the Lunar admin panel uses internally.

```php
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Lunar\Core\Models\Brand;
use Lunar\Filament\Schemas\Brand\BrandForm;
use Lunar\Filament\Schemas\Brand\BrandInfolist;
use Lunar\Filament\Tables\Brand\BrandTable;

class MyBrandResource extends Resource
{
    protected static ?string $model = Brand::class;

    public static function form(Schema $schema): Schema
    {
        return BrandForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BrandInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BrandTable::configure($table);
    }
}
```

Every schema/table class also exposes granular `getXxxFormComponent()` / `getXxxTableColumn()` static helpers, so you can pick a single field or column without inheriting the rest:

```php
$schema->components([
    BrandForm::getNameFormComponent(),
    BrandForm::getDescriptionFormComponent(),
    \Filament\Forms\Components\Toggle::make('featured'),    // your own field
]);
```

Models with a complete schema, table, infolist, and relation managers: `Activity`, `AttributeGroup`, `Brand`, `Channel`, `Collection`, `CollectionGroup`, `Currency`, `Customer`, `CustomerGroup`, `Discount`, `Language`, `Order`, `Product`, `ProductOption`, `ProductType`, `ProductVariant`, `Staff`, `Tag`, `TaxClass`, `TaxRate`, `TaxZone`.

---

## Actions

First-class Filament `Action` / `BulkAction` classes for every commerce verb the admin uses. The Filament class owns the modal schema, labels, notifications, and visibility predicate; the underlying business logic lives in a `Lunar\Core\Actions\*` counterpart so a CLI, API, or different UI can call the same code.

| Action | Wraps | Where it fits |
| --- | --- | --- |
| `Orders\RefundOrderAction` | `Core\Actions\Orders\RefundOrder` | Order header / detail page |
| `Orders\CaptureOrderAction` | `Core\Actions\Orders\CaptureOrder` | Order header / detail page |
| `Orders\CloseOrderAction` | `Core\Actions\Orders\CloseOrder` | Order header (archive a dealt-with order) |
| `Orders\ReopenOrderAction` | `Core\Actions\Orders\ReopenOrder` | Order header (un-archive a closed order) |
| `Orders\NotifyCustomerAction` | `Core\Actions\Orders\NotifyCustomer` | Order header (compose + send a customer notification from the order-scoped, sendable entries of the `OrderNotifications` catalogue; hidden when none are sendable) |
| `Orders\AddOrderNoteAction` | — (Filament-only single-field write) | Order header |
| `Orders\DownloadOrderPdfAction` | — (Filament-only, subclass of `Support\DownloadPdfAction`) | Order header |
| `Products\DuplicateProductAction` | `Core\Actions\Products\DuplicateProduct` | Product row / header |
| `Products\PublishProductsBulkAction` / `UnpublishProductsBulkAction` / `ArchiveProductsBulkAction` | `Core\Actions\Products\UpdateProductStatus` | Product table |
| `Products\AdjustStockAction` | `Core\Actions\Products\AdjustStock` | Variant row |
| `Collections\CreateRootCollectionAction` / `CreateChildCollectionAction` | `Core\Actions\Collections\CreateRootCollection` / `CreateChildCollection` | Collection tree view |
| `Collections\MoveCollectionAction` / `DeleteCollectionAction` | `Core\Actions\Collections\MoveCollection` / `DeleteCollection` | Collection tree view |

Drop into any header/row/bulk action array — they work like any Filament action:

```php
use Lunar\Filament\Actions\Orders\CaptureOrderAction;
use Lunar\Filament\Actions\Orders\CloseOrderAction;
use Lunar\Filament\Actions\Orders\RefundOrderAction;

protected function getDefaultHeaderActions(): array
{
    return [
        CaptureOrderAction::make(),
        RefundOrderAction::make(),
        CloseOrderAction::make(),
    ];
}
```

Need the verb outside Filament (e.g. an API endpoint)? Call the core action directly:

```php
use Lunar\Core\Actions\Orders\RefundOrder;

$result = RefundOrder::run(
    order: $order,
    transactionId: $transaction->id,
    amount: '25.00',
    notes: 'Customer requested partial refund',
);
```

The core action validates the amount against `RefundOrder::availableToRefund($order)`, dispatches through the underlying payment driver, and returns the driver's `PaymentRefund` result. Bulk Filament actions are thin loops over the same core action wrapped in a single transaction.

Shared `Concerns` traits (`InteractsWithTransactions`, `ConfirmsDestructiveAction`) cover the repeating form fragments — extend or override them in a subclass to bend the schema without re-implementing the whole action.

---

## Global search

Each searchable Lunar model has a `GlobalSearchDescriptor` subclass that owns its searchable attribute list, result title, result details, and eager-loaded query. Consumers compose their own Filament resource and opt in with one trait and one property:

```php
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

The trait forwards `getGloballySearchableAttributes`, `getGlobalSearchResultTitle`, `getGlobalSearchResultDetails`, and `getGlobalSearchEloquentQuery` to the descriptor, and routes the actual constraint-building through `RecordSearch` — the same Scout-vs-translated-attribute backend the entity selectors use. Scout is used when `lunar.panel.scout_enabled=true` and the model uses `Laravel\Scout\Searchable`; otherwise the query falls back to LIKE-matching across the resource's attribute list plus any searchable `TranslatedText` attributes.

`getGlobalSearchResultUrl` stays on the resource — only the resource knows its own URL.

Descriptors shipped: `OrderGlobalSearch`, `ProductGlobalSearch`, `CustomerGlobalSearch`, `CollectionGlobalSearch`, `BrandGlobalSearch`.

There is no panel-wide auto-registration to enable or disable — global search is opt-in per resource via the trait. A consumer who only wants Lunar models in their existing resources adds the trait there; one who omits it gets no Lunar global-search rows.

---

## Dashboard widgets

Drop into your panel's `widgets([…])` configuration:

```php
use Lunar\Filament\Widgets\Dashboard\Orders\AverageOrderValueChart;
use Lunar\Filament\Widgets\Dashboard\Orders\LatestOrdersTable;
use Lunar\Filament\Widgets\Dashboard\Orders\NewVsReturningCustomersChart;
use Lunar\Filament\Widgets\Dashboard\Orders\OrdersSalesChart;
use Lunar\Filament\Widgets\Dashboard\Orders\OrderStatsOverview;
use Lunar\Filament\Widgets\Dashboard\Orders\OrderTotalsChart;
use Lunar\Filament\Widgets\Dashboard\Orders\PopularProductsTable;

return $panel->widgets([
    OrderStatsOverview::class,
    OrdersSalesChart::class,
    OrderTotalsChart::class,
    AverageOrderValueChart::class,
    NewVsReturningCustomersChart::class,
    LatestOrdersTable::class,
    PopularProductsTable::class,
]);
```

Also available: `Lunar\Filament\Widgets\Customer\CustomerStatsOverviewWidget`, `Lunar\Filament\Widgets\Collections\CollectionTreeView`, `Lunar\Filament\Widgets\Products\ProductOptionsWidget`, `Lunar\Filament\Widgets\Products\VariantSwitcherTable`.

Widgets that link to Lunar records (e.g. "click an order in this table") use a record-URL resolver. Wire it up to your own resources in your panel provider:

```php
use Lunar\Filament\Support\Facades\RecordUrls;

RecordUrls::resolveUsing('order', fn ($order) => MyOrderResource::getUrl('view', ['record' => $order]));
RecordUrls::resolveUsing('product_variant', fn ($variant) => MyProductResource::getUrl('edit', ['record' => $variant->product]));
```

When no resolver is registered, the widget gracefully omits the link.

---

## Attribute system

Lunar's flexible-attributes system is configured per-product-type and rendered automatically by the `Attributes` form component. The shipped field types live under `Lunar\Filament\FieldTypes\*` — `TextField`, `TranslatedText`, `Dropdown`, `Toggle`, `Number`, `File`, `ListField`, `Vimeo`, `YouTube`. Each is auto-registered via the service provider.

Register your own field type:

```php
use Lunar\Filament\FieldTypes\BaseFieldType;
use Lunar\Filament\Support\Facades\AttributeData;

class ColorPicker extends BaseFieldType
{
    public function getFilamentComponent($attribute, $component) { /* … */ }
    public function getCast(): string { /* … */ }
}

AttributeData::registerFieldType(ColorPicker::class);
```

---

## Customisation strategies

Three ways to bend the bridge to your own UX, each with different upgrade implications.

| Approach | When to reach for it | Upgrade impact |
| --- | --- | --- |
| **Extension hooks** — `LunarFilament::extensions([…])` | Add or modify components on an existing schema without owning the file | Additive — bridge improvements still reach you on minor releases |
| **Subclass and rebind** — bind your subclass in the container | Fully replace a schema/table class without copying it | Full replacement — bridge improvements still reach the parent methods you don't override |
| **Publish stubs** — `vendor:publish --tag=lunar-filament.schemas` | Take complete ownership of one or more files in your app namespace | One-way door — bridge improvements no longer reach the published file; re-merge by hand |

### Extension hooks

```php
use Lunar\Filament\Schemas\Product\ProductForm;
use Lunar\Filament\Support\Facades\LunarFilament;

LunarFilament::extensions([
    ProductForm::class => new class {
        public function configureForm($schema)
        {
            return $schema->components([
                ...$schema->getComponents(),
                Filament\Forms\Components\Toggle::make('featured'),
            ]);
        }
    },
]);
```

Register your extensions in a service provider's `boot()` method. Hooks stack — register multiple extensions against the same target and each runs in registration order, passing its return value into the next.

### Subclass and rebind

```php
namespace App\Filament\Schemas\Product;

class ProductForm extends \Lunar\Filament\Schemas\Product\ProductForm
{
    public static function getBrandComponent(): \Filament\Schemas\Components\Component
    {
        return parent::getBrandComponent()->hidden();
    }
}

// In a service provider:
$this->app->bind(
    \Lunar\Filament\Schemas\Product\ProductForm::class,
    \App\Filament\Schemas\Product\ProductForm::class,
);
```

### Publish stubs

```bash
php artisan vendor:publish --tag=lunar-filament.schemas
```

Copies every schema, table, infolist, and relation manager into `app/Filament/…` (configurable in `config/lunar-filament.php` → `publish_path`). The runtime resolver prefers your published copy when both exist.

You can also publish:

```bash
php artisan vendor:publish --tag=lunar-filament.config    # config file
php artisan vendor:publish --tag=lunar-filament.lang      # translation files (16 locales)
php artisan vendor:publish --tag=lunar-filament.views     # blade views
```

---

## Configuration

Publish and tweak `config/lunar-filament.php` to change:

- `publish_path` — where stub publication writes files (default: `app/Filament`).
- `resolver.prefer_published` — runtime preference between published and bridge classes.
- `register_widgets_on_default_panel` — opt-in auto-registration of the dashboard widgets (off by default for downstream-panel installs).
- `record_url_resolvers` — closures that map a record + key to a URL inside your panel.

---

## Translations

The package ships translations in 16 locales: `ar`, `bg`, `de`, `en`, `es`, `fa`, `fr`, `hr`, `hu`, `mn`, `nl`, `pl`, `pt_BR`, `ro`, `tr`, `vi`. Override per-key by publishing the lang files (`php artisan vendor:publish --tag=lunar-filament.lang`) and editing `lang/vendor/lunar-filament/{locale}/…`.

---

## Standalone Filament panel example

A minimum panel that uses bridge components without Lunar's admin shell:

```php
use Filament\Panel;
use Filament\PanelProvider;
use Lunar\Filament\Widgets\Dashboard\Orders\OrderStatsOverview;
use Lunar\Filament\Widgets\Dashboard\Orders\OrdersSalesChart;

class MyPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('store')
            ->path('store')
            ->resources([
                MyBrandResource::class,        // uses Lunar\Filament\Schemas\Brand\BrandForm internally
                MyProductResource::class,
                MyOrderResource::class,
            ])
            ->widgets([
                OrderStatsOverview::class,
                OrdersSalesChart::class,
            ]);
    }
}
```

---

## Versioning

The bridge tracks Filament's release cadence. A Filament major (v5 → v6) drives a bridge major; the Lunar admin shell tightens its constraint when ready.

For v2 the package is developed inside the [Lunar monorepo](https://github.com/lunarphp/lunar). It extracts into its own repository (`lunarphp/filament`) at the v2.0.0 stable cut.

## License

MIT.
