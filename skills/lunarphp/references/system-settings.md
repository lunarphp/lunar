# System Settings

## Channels

```php
use Lunar\Models\Channel;

// Schedule product for a channel
$product->scheduleChannel($channel);
$product->scheduleChannel($channel, now()->addDays(7)); // With start date

// Query
Product::channel($channel)->get();
```

## Customer Groups

```php
use Lunar\Models\CustomerGroup;

$product->scheduleCustomerGroup($group);
$product->scheduleCustomerGroup($group, now()->addDays(7));

Product::customerGroup($group)->get();
```

## Currencies

```php
use Lunar\Models\Currency;

Currency::default()->first(); // Get default currency

// Sync pricing: Lunar auto-calculates prices from default currency
// using the exchange rate when enabled
```

## Tax Setup

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

> For a detailed walkthrough on configuring system settings after installation, see the [System Settings guide](https://docs.lunarphp.com/1.x/getting-started/setup/system-settings.md).

## References

- [Channels Reference](https://docs.lunarphp.com/1.x/reference/channels.md)
- [Currencies Reference](https://docs.lunarphp.com/1.x/reference/currencies.md)
- [Languages Reference](https://docs.lunarphp.com/1.x/reference/languages.md)
- [Taxation Reference](https://docs.lunarphp.com/1.x/reference/taxation.md)
- [Countries & States Reference](https://docs.lunarphp.com/1.x/reference/countries-states.md)
- [Customers Reference](https://docs.lunarphp.com/1.x/reference/customers.md)
- [Tags Reference](https://docs.lunarphp.com/1.x/reference/tags.md)
- [Activity Log Reference](https://docs.lunarphp.com/1.x/reference/activity-log.md)
