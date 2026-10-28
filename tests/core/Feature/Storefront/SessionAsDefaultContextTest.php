<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Lunar\Core\Facades\Pricing;
use Lunar\Core\Facades\StorefrontSession;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Tests\Core\Stubs\User;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

// What a storefront starter kit ships; core provides only the call inside it.
class UseSessionStorefrontContext
{
    public function handle($request, Closure $next)
    {
        StorefrontSession::useAsDefaultContext();

        return $next($request);
    }
}

beforeEach(function () {
    Channel::factory()->create(['default' => true]);
    Language::factory()->create(['default' => true]);
    CustomerGroup::factory()->create(['default' => true]);
    $usd = Currency::factory()->create(['default' => true, 'code' => 'USD', 'exchange_rate' => 1]);
    $gbp = Currency::factory()->create(['default' => false, 'code' => 'GBP']);
    $trade = CustomerGroup::factory()->create(['default' => false]);

    $variant = ProductVariant::factory()->create([
        'product_id' => Product::factory()->create(['status' => 'published'])->id,
    ]);

    foreach ([[100, $usd, null], [70, $usd, $trade], [90, $gbp, null]] as [$price, $currency, $group]) {
        Price::factory()->create([
            'price' => $price,
            'priceable_type' => $variant->getMorphClass(),
            'priceable_id' => $variant->id,
            'currency_id' => $currency->id,
            'customer_group_id' => $group?->id,
            'min_quantity' => 1,
        ]);
    }

    $customer = Customer::factory()->create();
    $customer->customerGroups()->attach($trade);
    $this->user = User::factory()->create();
    $this->user->customers()->sync($customer->id);

    $bare = fn () => Pricing::for($variant)->get()->matched->price;

    Route::middleware(['web', UseSessionStorefrontContext::class])->get('/_storefront/price', $bare);
    Route::middleware('web')->get('/_plain/price', $bare);
    Route::middleware(['web', UseSessionStorefrontContext::class])->get('/_storefront/switch', function () use ($bare, $gbp) {
        StorefrontSession::setCurrency($gbp);

        return $bare();
    });
});

test('behind the middleware a bare call prices for the logged-in customer', function () {
    $this->actingAs($this->user)->get('/_storefront/price')->assertOk()->assertContent('70');
});

test('a guest behind the middleware gets the default group price', function () {
    $this->get('/_storefront/price')->assertOk()->assertContent('100');
});

test('a route that does not nominate the session gets store defaults', function () {
    $this->actingAs($this->user)->get('/_plain/price')->assertOk()->assertContent('100');
});

test('a currency switch made after the middleware ran is seen', function () {
    $this->get('/_storefront/switch')->assertOk()->assertContent('90');
});
