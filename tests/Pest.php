<?php

use Illuminate\Support\Facades\Config;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\DataTypes\ShippingOption;
use Lunar\Core\Facades\ShippingManifest;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CartAddress;
use Lunar\Core\Models\CartLine;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\TaxClass;
use Lunar\Tests\Core\Stubs\User;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

// uses(Tests\TestCase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

// expect()->extend('toBeOne', function () {
//     return $this->toBe(1);
// });

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function setAuthUserConfig(): void
{
    Config::set('auth.providers.users.model', User::class);
}

function buildCart(array $cartParams = []): Cart
{
    Language::factory()->create([
        'default' => true,
    ]);

    $currency = Currency::factory()->create([
        'default' => true,
    ]);

    $taxClass = TaxClass::factory()->create();

    $cart = Cart::factory()->create(array_merge([
        'currency_id' => $currency->id,
    ], $cartParams));

    ShippingManifest::addOption(
        new ShippingOption(
            name: 'Basic Delivery',
            description: 'Basic test delivery',
            identifier: 'BASDEL',
            price: new PriceValue(500, $cart->currency),
            taxClass: $taxClass
        )
    );

    CartAddress::factory()->create([
        'cart_id' => $cart->id,
        'shipping_option' => 'BASDEL',
    ]);

    CartAddress::factory()->create([
        'cart_id' => $cart->id,
        'type' => 'billing',
    ]);

    $variant = ProductVariant::factory()->create()->each(function ($variant) use ($currency) {
        $variant->prices()->create([
            'price' => 1.99,
            'currency_id' => $currency->id,
        ]);
    });

    CartLine::factory()->create([
        'cart_id' => $cart->id,
        'purchasable_id' => $variant,
    ]);

    return $cart;
}
