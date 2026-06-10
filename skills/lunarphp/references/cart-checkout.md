# Cart, Checkout & Orders

## CartSession Facade

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

## Cart Model Directly

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

> For a complete checkout flow walkthrough, see the [Checkout guide](https://docs.lunarphp.com/1.x/guides/checkout.md).
> For building a cart page with line items, quantities, coupons, and totals, see the [Cart guide](https://docs.lunarphp.com/1.x/guides/cart.md).

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

> See the [Order History guide](https://docs.lunarphp.com/1.x/guides/order-history.md) for building customer-facing order history pages.

## References

- [Carts Reference](https://docs.lunarphp.com/1.x/reference/carts.md)
- [Orders Reference](https://docs.lunarphp.com/1.x/reference/orders.md)
- [Addresses Reference](https://docs.lunarphp.com/1.x/reference/addresses.md)
