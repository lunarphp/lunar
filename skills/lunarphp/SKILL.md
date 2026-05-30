---
name: lunarphp
description: "Develops e-commerce features with LunarPHP. Activates when working with products, variants, cart, checkout, orders, discounts, payments, pricing, search, collections, attributes, or any LunarPHP model/facade/config. Also activates when the user mentions Lunar, LunarPHP, product catalog, e-commerce cart, order management, or storefront checkout in a Laravel project. Make sure to use this skill whenever the user works with e-commerce functionality, even if they don't explicitly mention Lunar."
license: MIT
metadata:
  author: lunarphp
---

# LunarPHP E-Commerce Development

## Documentation

LunarPHP docs are at `https://docs.lunarphp.com/1.x/`. The LLMS index is at `https://docs.lunarphp.com/llms.txt`.

Use `search-docs` for pattern-specific queries — Lunar docs are indexed by the search tool.

## When to Apply

Activate this skill when:

- Installing or configuring LunarPHP in a Laravel app
- Creating or modifying products, variants, product options, brands, or collections
- Building cart functionality (add/update/remove items, coupons, totals)
- Implementing checkout flow (addresses, shipping, payment, order creation)
- Managing orders, order lines, addresses, transactions, and refunds
- Setting up discounts (AmountOff, BuyXGetY, custom types)
- Integrating payment drivers (Stripe, PayPal, offline, custom)
- Configuring pricing pipelines, formatting, and customer group pricing
- Setting up search indexing with Scout for Lunar models
- Working with attributes, attribute groups, and custom field types
- Extending Lunar models with dynamic relationships or model replacement
- Configuring channels, currencies, languages, tax zones, and customer groups
- Building storefront features: catalog menu, PLP, PDP, cart, checkout

## Key Concepts

### Core Models & Namespaces

All Lunar models live under the `Lunar\Models` namespace:

| Model | Purpose |
|-------|---------|
| `Product` | Core product with variants, options, attributes |
| `ProductVariant` | Purchasable variant with pricing, stock, dimensions |
| `ProductOption` | Option definitions (e.g. "Color", "Size") |
| `ProductOptionValue` | Individual values for options |
| `ProductType` | Determines which attributes are available per product |
| `Brand` | Optional brand association |
| `Collection` | Hierarchical product groups via nested sets |
| `CollectionGroup` | Top-level container for collections |
| `Cart` | Shopping cart with lines, addresses, calculated totals |
| `CartLine` | Line item in a cart, linked to a purchasable |
| `CartAddress` | Shipping/billing address on a cart |
| `Order` | Completed or draft purchase from a cart |
| `OrderLine` | Line item in an order |
| `OrderAddress` | Address on an order |
| `Transaction` | Payment transaction (capture, intent, refund) |
| `Discount` | Discount/coupon with type-specific data |
| `Price` | Pricing per variant, currency, customer group, quantity break |
| `Currency` | Currency configuration with exchange rates |
| `Channel` | Sales outlet (webstore, mobile, wholesale) |
| `CustomerGroup` | Customer segment for pricing/visibility |
| `TaxClass` | Tax categorization for products |
| `TaxZone` | Geographic region for tax calculation |
| `TaxRate` | Tax rate within a zone |
| `Attribute` | Custom data field definition |
| `AttributeGroup` | Logical grouping of attributes |
| `Country` | Country reference data |
| `Url` | SEO-friendly URL slugs (polymorphic) |

### Key Facades

| Facade | Purpose |
|--------|---------|
| `Lunar\Facades\CartSession` | Session-based cart management |
| `Lunar\Facades\Pricing` | Fetch correct price for variant/criteria |
| `Lunar\Facades\Payments` | Payment driver management |
| `Lunar\Facades\ShippingManifest` | Shipping option retrieval |
| `Lunar\Facades\Discounts` | Discount application and coupon validation |
| `Lunar\Facades\PricingManager` | Pricing pipeline management |

### Config Files

All under `config/lunar/`. Key files:

- `cart.php` — Cart behavior, pipelines, validators, fingerprint generator, pruning
- `cart_session.php` — Session key, auto-create, multiple orders per cart
- `payments.php` — Payment type definitions and driver mapping
- `pricing.php` — Pricing pipelines and formatter
- `orders.php` — Order pipelines, reference generator, statuses
- `search.php` — Searchable models, engine map, indexers
- `taxes.php` — Tax driver selection
- `database.php` — Table prefix, user ID field type
- `discounts.php` — Coupon validator class
- `shipping.php` — Shipping configuration and units of measure
- `media.php` — Media/image handling

The database uses a `lunar_` table prefix (configurable in `config/lunar/database.php`). All prices are stored as integers (smallest currency unit, e.g. cents).

## Installation & Setup

### Requirements

- PHP >= 8.3
- Laravel 12 or 13
- MySQL 8.0+ / PostgreSQL 9.4+
- `bcmath`, `exif`, `intl` PHP extensions

### Step-by-Step

1. Install via Composer:

```bash
composer require lunarphp/lunar:"^1.0" -W
```

2. Add `LunarUser` trait and interface to the `User` model:

```php
use Lunar\Base\Traits\LunarUser;
use Lunar\Base\LunarUser as LunarUserInterface;

class User extends Authenticatable implements LunarUserInterface
{
    use LunarUser;
}
```

3. Register the admin panel in `AppServiceProvider`:

```php
use Lunar\Admin\Support\Facades\LunarPanel;

public function register(): void
{
    LunarPanel::register();
}
```

4. Run the installer:

```bash
php artisan lunar:install
```

The installer publishes configs, runs migrations, creates an admin user, and seeds default data (channel, language, currency, customer group, etc.).

5. Access the admin panel at `/lunar`.

### Post-Install Configuration

Publish configs before installing to customize settings:

```bash
php artisan vendor:publish --tag=lunar
```

Key config values to check:

```php
// config/lunar/database.php
'table_prefix' => 'lunar_',
'users_id_type' => 'bigint', // or 'int', 'uuid'

// config/lunar/cart_session.php
'session_key' => 'lunar_cart',
'auto_create' => false,

// config/lunar/payments.php
'default' => env('PAYMENTS_TYPE', 'cash-in-hand'),
```

## Products & Catalog

### Products

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

### Variants

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

### Product Options

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

### Product Types

Product types determine which attributes are available for editing:

```php
use Lunar\Models\ProductType;

$productType = ProductType::create(['name' => 'Boots']);
$productType->mappedAttributes()->attach($attributeIds);
```

### Brands

```php
use Lunar\Models\Brand;

$brand = Brand::create(['name' => 'Nike']);
$product->update(['brand_id' => $brand->id]);
```

### Associations

Link products as cross-sells, up-sells, or alternates:

```php
$product->associations()->create([
    'association_type' => 'cross-sell', // or 'up-sell', 'alternate'
    'target_id' => $targetProduct->id,
]);
```

### Pricing

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

### Collections

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

## Cart

### CartSession Facade

Use `CartSession` for session-based cart management (recommended for traditional storefronts):

```php
use Lunar\Facades\CartSession;

// Get current cart (returns null if none exists and auto_create is false)
$cart = CartSession::current();

// Add item
CartSession::add($purchasable, quantity: 2, meta: ['personalization' => 'Happy Birthday!']);

// Add multiple
CartSession::addLines([
    ['purchasable' => $variant1, 'quantity' => 2],
    ['purchasable' => $variant2, 'quantity' => 1],
]);

// Update line
CartSession::updateLine($cartLineId, quantity: 3, meta: ['foo' => 'bar']);

// Remove line
CartSession::remove($cartLineId);

// Clear cart
CartSession::clear();

// Create order
$order = CartSession::createOrder();

// Forget cart (removes from session and soft-deletes)
CartSession::forget();

// Associate user
CartSession::associate($cart, $user, policy: 'merge');
```

### Cart Model Directly

```php
use Lunar\Models\Cart;

// Create cart
$cart = Cart::create([
    'currency_id' => $currency->id,
    'channel_id' => $channel->id,
]);

// Add lines
$cart->add($purchasable, quantity: 2, meta: ['key' => 'value']);

$cart->addLines([
    ['purchasable' => $variant1, 'quantity' => 2],
    ['purchasable' => $variant2, 'quantity' => 1],
]);

// Update/remove
$cart->updateLine($lineId, quantity: 5);
$cart->remove($lineId);
$cart->clear();

// Addresses
$cart->setShippingAddress([/* address fields */]);
$cart->setBillingAddress([/* address fields */]);

// Shipping option
$cart->setShippingOption($shippingOption);

// Coupon
$cart->update(['coupon_code' => '20OFF']);
$cart->recalculate();

// Calculate totals
$cart->calculate();
$cart->recalculate(); // Force
$cart->isCalculated();

// Check order readiness
$cart->canCreateOrder();
$cart->hasCompletedOrders();

// Create order
$order = $cart->createOrder();
$order = $cart->createOrder(allowMultipleOrders: true);
$order = $cart->createOrder(orderIdToUpdate: $existingOrderId);

// Stock validation
$cart->validateStock();

// Fingerprint (detect cart changes)
$fingerprint = $cart->fingerprint();
$cart->checkFingerprint($fingerprint); // Throws FingerprintMismatchException
```

### Cart Line Properties (after calculation)

| Property | Description |
|----------|-------------|
| `unitPrice` | Single unit price (excl. tax) |
| `unitPriceInclTax` | Single unit price (incl. tax) |
| `subTotal` | Line total before discounts and tax |
| `subTotalDiscounted` | Line total after discounts, before tax |
| `discountTotal` | Discount applied to this line |
| `taxAmount` | Tax for this line |
| `total` | Final line total (incl. tax and discounts) |

### Cart Properties (after calculation)

| Property | Description |
|----------|-------------|
| `subTotal` | Sum of line subtotals, before tax/discounts |
| `subTotalDiscounted` | Subtotal after line-level discounts |
| `discountTotal` | Total discounts applied |
| `discountBreakdown` | Collection of discount breakdowns |
| `taxTotal` | Total tax across all lines and shipping |
| `taxBreakdown` | Tax breakdown by rate |
| `shippingSubTotal` | Shipping cost before tax |
| `shippingTaxTotal` | Tax on shipping |
| `shippingTotal` | Shipping cost including tax |
| `total` | Final cart total |

### Cart Scopes

```php
Cart::unmerged()->get();  // Carts not merged into another
Cart::active()->get();    // Carts with no completed orders
```

### User Login Handling

Lunar listens to auth events and handles cart association automatically. Config `auth_policy` in `config/lunar/cart.php` controls `merge` vs `override` behavior when a user logs in.

## Checkout

### Flow

1. Set addresses on cart
2. Select shipping option
3. Review order
4. Create order from cart
5. Process payment

### Setting Addresses

```php
$cart->setBillingAddress([
    'country_id' => $country->id,
    'first_name' => 'John',
    'last_name' => 'Doe',
    'line_one' => '123 Main St',
    'city' => 'London',
    'postcode' => 'NW1 1WN',
    'contact_email' => 'john@example.com',
]);

$cart->setShippingAddress([
    // same structure
]);

// Reuse billing for shipping
$cart->setShippingAddress($cart->shippingAddress);
```

Required fields for order creation: `country_id`, `first_name`, `line_one`, `city`, `postcode`.

### Shipping Options

```php
use Lunar\Facades\ShippingManifest;

// Get available options (requires shipping address on cart)
$options = ShippingManifest::getOptions($cart);

// Set selected option
$option = ShippingManifest::getOption($cart, 'standard-delivery');
$cart->setShippingOption($option);
```

### Creating Order

```php
use Lunar\Exceptions\Carts\CartException;

try {
    $order = $cart->createOrder();
} catch (CartException $e) {
    $e->errors(); // MessageBag with validation failures
}
```

### Estimated Shipping (before full address)

```php
$shippingOption = $cart->getEstimatedShipping([
    'postcode' => '123456',
    'state' => 'Essex',
    'country' => Country::first(),
], setOverride: true);
```

## Orders

### Order Model

```php
use Lunar\Models\Order;

$order->isDraft();  // placed_at is null
$order->isPlaced(); // placed_at is set

// Relationships
$order->lines;
$order->productLines;     // Excluding shipping
$order->shippingLines;
$order->physicalLines;
$order->digitalLines;
$order->addresses;
$order->shippingAddress;
$order->billingAddress;
$order->transactions;
$order->captures;
$order->intents;
$order->refunds;
$order->currency;  // Matched on currency_code
$order->cart;
$order->customer;
$order->user;
```

Order fields all use `Lunar\DataTypes\Price` casting: `sub_total`, `discount_total`, `shipping_total`, `tax_total`, `total`.

### Order Reference Generation

Default format: `{prefix}{order_id_padded_to_8_digits}`. Customize via `config/lunar/orders.php`:

```php
'reference_generator' => App\Generators\MyCustomGenerator::class,
```

### Order Status Notifications

Configure mailers per status in `config/lunar/orders.php`:

```php
'statuses' => [
    'awaiting-payment' => [
        'label' => 'Awaiting Payment',
        'color' => '#848a8c',
        'mailers' => [App\Mail\MyMailer::class],
        'notifications' => [],
    ],
],
```

### Transactions

```php
use Lunar\Models\Transaction;

$order->transactions()->create([
    'success' => true,
    'type' => 'capture', // capture, intent, refund
    'driver' => 'stripe',
    'amount' => 1999,
    'reference' => 'ch_123456',
    'status' => 'settled',
    'card_type' => 'visa',
    'last_four' => '4242',
]);
```

## Payments

### Payment Drivers

Lunar uses a driver-based system. Built-in: `offline`. First-party: Stripe.

```php
use Lunar\Facades\Payments;

$payment = Payments::driver('card')
    ->cart($cart)
    ->withData(['payment_token' => $token])
    ->authorize();

$payment->success;  // bool
$payment->message;  // ?string
$payment->orderId;  // ?int
```

Configuration in `config/lunar/payments.php`:

```php
'types' => [
    'cash-in-hand' => [
        'driver' => 'offline',
        'authorized' => 'payment-offline',
    ],
    'card' => [
        'driver' => 'stripe',
        'authorized' => 'payment-received',
    ],
],
```

### Payment Lifecycle

1. Authorize: `Payments::driver('card')->cart($cart)->authorize()`
2. Capture: The driver typically handles this during authorization
3. Refund: Via the payment provider or manually in admin

The `PaymentAttemptEvent` is dispatched on every payment attempt.

## Discounts

### Built-in Types

1. **AmountOff** — Percentage or fixed amount off
2. **BuyXGetY** — Buy X, get Y free promotions

### Creating a Discount

```php
use Lunar\Models\Discount;

$discount = Discount::create([
    'name' => '20% Off',
    'handle' => '20_percent_off',
    'coupon' => '20OFF',
    'type' => 'Lunar\DiscountTypes\AmountOff',
    'data' => [
        'fixed_value' => false,
        'percentage' => 20,
        'min_prices' => ['USD' => 2000],
    ],
    'starts_at' => now(),
    'ends_at' => null,
    'max_uses' => null,
    'max_uses_per_user' => 1,
    'priority' => 1,
    'stop' => false,
]);
```

### Discountable Types

| Type | Purpose |
|------|---------|
| `condition` | Product must be in cart for discount to activate |
| `exclusion` | Product is excluded from discount |
| `limitation` | Discount only applies to these products |
| `reward` | Reward products (for BuyXGetY) |

```php
$discount->discountableConditions()->create([
    'discountable_type' => 'product_variant',
    'discountable_id' => $variant->id,
]);
```

### Discount Statuses

```php
$discount->status; // 'active', 'pending', 'expired', 'scheduled'
```

### Coupon Validation

```php
use Lunar\Facades\Discounts;

Discounts::validateCoupon('20OFF'); // bool
Discounts::resetDiscounts(); // Clear cached discounts
```

### Custom Discount Types

```php
use Lunar\DiscountTypes\AbstractDiscountType;
use Lunar\Facades\Discounts;

class CustomDiscount extends AbstractDiscountType
{
    public function getName(): string
    {
        return 'Custom Discount';
    }

    public function apply(Cart $cart): Cart
    {
        return $cart;
    }
}

Discounts::addType(CustomDiscount::class);
```

## Pricing

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

Configured in `config/lunar/pricing.php`. Custom pipelines can modify resolved pricing:

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

## Search

Lunar search is built on Laravel Scout with pre-configured indexers per model.

### Configuration

```php
// config/lunar/search.php
'models' => [
    Lunar\Models\Product::class,
    Lunar\Models\Collection::class,
    // ...
],
'engine_map' => [
    Lunar\Models\Product::class => 'typesense',
    Lunar\Models\Order::class => 'meilisearch',
],
'indexers' => [
    Lunar\Models\Product::class => Lunar\Search\ProductIndexer::class,
    // ...
],
```

Requires `soft_delete: true` in `config/scout.php`.

### Indexing

```bash
php artisan lunar:search:index
php artisan lunar:search:index "Lunar\Models\Product" --refresh
php artisan lunar:search:index "Lunar\Models\Order" --flush
```

For Meilisearch, run setup to configure filterable/sortable attributes:

```bash
php artisan lunar:meilisearch:setup
```

### Storefront Search Add-on

For faceted search with consistent API across engines:

```bash
composer require lunarphp/search
```

```php
use Lunar\Search\Facades\Search;

$results = Search::search('boots')
    ->paginate(20)
    ->get();
```

## Attributes

### Attribute System

Custom data stored as JSON (`attribute_data`) with typed field values.

### Field Types

| Type | Description |
|------|-------------|
| `Lunar\FieldTypes\Text` | Plain or rich text |
| `Lunar\FieldTypes\TranslatedText` | Translatable text (one value per locale) |
| `Lunar\FieldTypes\Number` | Integer or decimal |
| `Lunar\FieldTypes\Toggle` | Boolean |
| `Lunar\FieldTypes\Dropdown` | Single select from options |
| `Lunar\FieldTypes\ListField` | Reorderable list of text values |
| `Lunar\FieldTypes\File` | File references |
| `Lunar\FieldTypes\YouTube` | YouTube video ID/URL |
| `Lunar\FieldTypes\Vimeo` | Vimeo video ID/URL |

### Saving & Reading

```php
use Lunar\FieldTypes\Text;
use Lunar\FieldTypes\TranslatedText;

$product->attribute_data = collect([
    'meta_title' => new Text('Best Screwdriver'),
    'description' => new TranslatedText(collect([
        'en' => new Text('Great tool'),
        'fr' => new Text('Super outil'),
    ])),
]);
$product->save();

// Read
$product->translateAttribute('name');       // Current locale
$product->translateAttribute('name', 'fr'); // Specific locale
$product->attr('name');                      // Shorthand
```

### Custom Attributable Models

```php
use Lunar\Base\Casts\AsAttributeData;
use Lunar\Base\Traits\HasAttributes;
use Lunar\Facades\AttributeManifest;

class MyModel extends Model
{
    use HasAttributes;

    protected $casts = [
        'attribute_data' => AsAttributeData::class,
    ];
}

// Register in service provider
AttributeManifest::addType(MyModel::class);
```

## System Settings

### Channels

```php
use Lunar\Models\Channel;

// Schedule product for a channel
$product->scheduleChannel($channel);
$product->scheduleChannel($channel, now()->addDays(7)); // With start date

// Query
Product::channel($channel)->get();
```

### Customer Groups

```php
use Lunar\Models\CustomerGroup;

$product->scheduleCustomerGroup($group);
$product->scheduleCustomerGroup($group, now()->addDays(7));

Product::customerGroup($group)->get();
```

### Currencies

```php
use Lunar\Models\Currency;

Currency::default()->first(); // Get default currency

// Sync pricing: Lunar auto-calculates prices from default currency
// using the exchange rate when enabled
```

### Tax Setup

```php
use Lunar\Models\TaxZone;
use Lunar\Models\TaxClass;

// Tax zone with country, states, or postcodes
$ukZone = TaxZone::create([
    'name' => 'UK VAT',
    'zone_type' => 'country',
    'price_display' => 'tax_inclusive', // or 'tax_exclusive'
]);

$rate = $ukZone->taxRates()->create(['name' => 'VAT', 'priority' => 1]);
$rate->taxRateAmounts()->create([
    'tax_class_id' => TaxClass::default()->first()->id,
    'percentage' => 20.000,
]);
```

## Extending Lunar

### Dynamic Relationships (Recommended)

```php
use Lunar\Models\Order;
use App\Models\Ticket;

Order::resolveRelationUsing('ticket', function ($orderModel) {
    return $orderModel->belongsTo(Ticket::class, 'ticket_id');
});
```

Register in a service provider's `boot` method.

### Model Replacement

For deeper customization (overriding methods, adding scopes):

```php
use Lunar\Facades\ModelManifest;

// Single model
ModelManifest::replace(
    Lunar\Models\Contracts\Product::class,
    App\Model\Product::class,
);

// Scan directory
ModelManifest::addDirectory(__DIR__.'/../Models');
```

Custom model must extend the Lunar model. Contract-based type hints work with route binding and relationships.

### Extending Cart Calculations

Define custom pipelines in `config/lunar/cart.php`:

```php
'pipelines' => [
    App\Pipelines\Cart\CustomCartPipeline::class,
],
```

Override order creation action:

```php
'actions' => [
    'order_create' => App\Actions\Carts\CustomCreateOrder::class,
],
```

Add custom validators:

```php
'validators' => [
    'order_create' => [App\Validation\Cart\CustomValidator::class],
],
```

## Key Artisan Commands

| Command | Purpose |
|---------|---------|
| `lunar:install` | Run the Lunar installer |
| `lunar:search:index` | Import/refresh search indexes |
| `lunar:meilisearch:setup` | Configure Meilisearch filterable/sortable fields |
| `lunar:update` | Handle Lunar updates and migrations |
| `vendor:publish --tag=lunar` | Publish Lunar configs |
| `vendor:publish --tag=lunarpanel.pdf` | Publish order PDF template |

## Common Pitfalls

- **`name` attribute required**: Internally expects `name` in product/collection attribute data. Missing it causes admin panel errors.
- **Prices stored as integers**: Always store prices in smallest currency unit (cents/pence). A $19.99 price is `1999`.
- **Cart prices are dynamic**: Not stored in DB until order is created. Always call `calculate()` or `recalculate()`.
- **`MissingCurrencyPriceException`**: Thrown when fetching price for a currency with no pricing defined for the variant.
- **Cart validation exceptions**: All throw `CartException`. Catch and display `$e->errors()` for detailed messages.
- **Single order per cart by default**: Pass `allowMultipleOrders: true` to `createOrder()` for split shipments.
- **`canCreateOrder()` before `createOrder()`**: Check readiness without throwing exceptions.
- **Fingerprint mismatch**: Use `checkFingerprint()` before payment to detect cart changes between checkout steps.
- **Model replacement conflicts**: Add-ons must never replace core models. Use dynamic relationships instead.
- **Scout `soft_delete`**: Must be `true` in `config/scout.php` or soft-deleted models appear in search results.
- **Engine map fallback**: Models not in `engine_map` use the default `SCOUT_DRIVER`. Configure per model to control indexing costs.
- **`scheduleCustomerGroup` / `scheduleChannel` with no dates**: Enables immediately. Pass `null` for `ends_at` to never expire.
- **Root collection required**: At least one root collection must exist before child collections can be created.
- **Tax zone clearance on address set**: Setting shipping address clears explicit `tax_zone_id`. Pass `clearTaxZone: false` to keep it.
- **BuyXGetY collection conditions**: Match against entire collections (added in 1.5) instead of listing individual products.
- **`resetDiscounts()` after coupon application**: Discounts are cached per request. Call this after applying a coupon to refresh.
