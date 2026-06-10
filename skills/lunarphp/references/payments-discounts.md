# Payments & Discounts

## Payment Drivers

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

> For a complete payment integration walkthrough with Stripe, see the [Payment Integration guide](https://docs.lunarphp.com/1.x/guides/payment-integration.md).

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

## References

- [Payments Reference](https://docs.lunarphp.com/1.x/reference/payments.md)
- [Discounts Reference](https://docs.lunarphp.com/1.x/reference/discounts.md)
