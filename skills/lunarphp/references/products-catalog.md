# Products & Catalog

## Products

Products always have at least one variant. Custom data is stored as `attribute_data` (JSON) using field types:

```php
use Lunar\Models\Product;
use Lunar\FieldTypes\Text;
use Lunar\FieldTypes\TranslatedText;

Product::create([
    'product_type_id' => $productType->id,
    'status' => 'published',
    'attribute_data' => [
        'name' => new TranslatedText(collect([
            'en' => new Text('FooBar'),
        ])),
        'description' => new Text('Description text.'),
    ],
]);
```

The `name` attribute is required — Lunar expects it internally.

Key scopes: `status($status)`, `channel($channel)`, `customerGroup($group)`.

## Variants

Variants hold pricing, stock, dimensions, and product identifiers:

```php
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;
use Lunar\Models\Currency;

$variant = ProductVariant::create([
    'product_id' => $product->id,
    'tax_class_id' => $taxClass->id,
    'sku' => 'BLUE-001',
    'unit_quantity' => 1,
    'min_quantity' => 1,
    'quantity_increment' => 1,
    'stock' => 100,
    'purchasable' => 'always', // or 'in_stock'
    'shippable' => true,
]);

$variant->prices()->create([
    'price' => 1999, // $19.99 in cents
    'currency_id' => $currency->id,
]);
```

Key fields: `sku`, `gtin`, `mpn`, `ean`, `unit_quantity`, `stock`, `backorder`, `purchasable`, dimensions (`length_value`, `width_value`, `height_value`, `weight_value`, `volume_value`).

## Product Options

Define variations like color/size:

```php
use Lunar\Models\ProductOption;

$color = ProductOption::create([
    'name' => ['en' => 'Color'],
    'label' => ['en' => 'Color'],
]);

$blue = $color->values()->create([
    'name' => ['en' => 'Blue'],
]);

$variant->values()->attach($blue);
```

Both `ProductOption` and `ProductOptionValue` have a `meta` JSON field for custom data (e.g. hex color values).

## Product Types

Product types determine which attributes are available for editing:

```php
use Lunar\Models\ProductType;

$productType = ProductType::create(['name' => 'Boots']);
$productType->mappedAttributes()->attach($attributeIds);
```

## Brands

```php
use Lunar\Models\Brand;

$brand = Brand::create(['name' => 'Nike']);
$product->update(['brand_id' => $brand->id]);
```

## Associations

Link products as cross-sells, up-sells, or alternates:

```php
$product->associations()->create([
    'association_type' => 'cross-sell', // or 'up-sell', 'alternate'
    'target_id' => $targetProduct->id,
]);
```

## Pricing

Prices are stored as integers (smallest currency unit). Use the `Pricing` facade for retrieval:

```php
use Lunar\Facades\Pricing;

$pricing = Pricing::qty(5)
    ->customerGroup($group)
    ->currency($currency)
    ->for($variant)
    ->get();

$pricing->matched;  // The matched Price model
$pricing->base;     // The base Price model
$pricing->priceBreaks; // Collection of quantity break prices
```

Or via the model:

```php
$pricing = $variant->pricing()->qty(5)->get();
```

### Price Data Type

All monetary values return `Lunar\DataTypes\Price` objects with:

```php
$price->value;                    // Raw integer (e.g. 1999)
$price->decimal();                // Float (e.g. 19.99)
$price->unitDecimal();            // Per unit (factors unit_quantity)
$price->formatted('en-gb');       // Formatted string (e.g. "£19.99")
$price->unitFormatted('en-gb');   // Per-unit formatted string
```

### Price Model

```php
use Lunar\Models\Price;

Price::create([
    'price' => 1999,
    'compare_price' => 2999,
    'currency_id' => $currency->id,
    'min_quantity' => 1,
    'customer_group_id' => null, // null = all groups
    'priceable_type' => $variant->getMorphClass(),
    'priceable_id' => $variant->id,
]);
```

### Tax Helpers

```php
$price->priceExTax();
$price->priceIncTax();
$price->comparePriceIncTax();

// With specific tax zone override (added in 1.5)
$price->priceIncTax($taxZone);
```

### Pricing Pipelines

Configured in `config/lunar/pricing.php`:

```php
'pipelines' => [
    App\Pipelines\Pricing\CustomPricingPipeline::class,
],
```

### Custom Formatter

```php
use Lunar\Pricing\PriceFormatterInterface;

class CustomFormatter implements PriceFormatterInterface
{
    public function __construct(
        public int $value,
        public ?Currency $currency = null,
        public int $unitQty = 1
    ) {}
    // Implement decimal(), unitDecimal(), formatted(), unitFormatted()
}
```

Register in `config/lunar/pricing.php`:

```php
'formatter' => App\Pricing\CustomFormatter::class,
```

## Collections

Collections use nested sets for hierarchy. Always create a root collection before children:

```php
use Lunar\Models\Collection;
use Lunar\Models\CollectionGroup;

$group = CollectionGroup::create([
    'name' => 'Main Catalogue',
    'handle' => 'main-catalogue',
]);

$parent = Collection::create([
    'collection_group_id' => $group->id,
    'attribute_data' => ['name' => new \Lunar\FieldTypes\Text('Clothing')],
]);

$child = Collection::create([
    'collection_group_id' => $group->id,
    'attribute_data' => ['name' => new \Lunar\FieldTypes\Text('T-Shirts')],
]);

$parent->appendNode($child);
```

Add products with sort position:

```php
$collection->products()->sync([
    $productA->id => ['position' => 1],
]);
```

Sort options: `min_price:asc`, `min_price:desc`, `sku:asc`, `sku:desc`, `custom`.

> For a complete walkthrough on building a catalog navigation menu, including active state tracking and caching, see the [Catalog Menu guide](https://docs.lunarphp.com/1.x/guides/catalog-menu.md).
> For product listing pages with filtering, sorting, and pagination, see the [Product Listing Page guide](https://docs.lunarphp.com/1.x/guides/product-listing-page.md).
> For product detail pages with variant selection and add-to-cart, see the [Product Display Page guide](https://docs.lunarphp.com/1.x/guides/product-display-page.md).

## References

- [Products Reference](https://docs.lunarphp.com/1.x/reference/products.md)
- [Collections Reference](https://docs.lunarphp.com/1.x/reference/collections.md)
- [Pricing Reference](https://docs.lunarphp.com/1.x/reference/pricing.md)
- [Associations Reference](https://docs.lunarphp.com/1.x/reference/associations.md)
