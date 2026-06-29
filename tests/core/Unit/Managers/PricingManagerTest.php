<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Contracts\Actions\Storefront\ResolvesStorefrontContext;
use Lunar\Core\DataObjects\PricingResponse;
use Lunar\Core\Managers\PricingManager;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Tests\Core\Stubs\TestPricingPipeline;
use Lunar\Tests\Core\Stubs\User;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

test('can initialise the manager', function () {
    expect(app(PricingManager::class))->toBeInstanceOf(PricingManager::class);
});

test('can set up available guest pricing', function () {
    $manager = app(PricingManager::class);

    $currency = Currency::factory()->create([
        'default' => true,
        'exchange_rate' => 1,
    ]);

    $product = Product::factory()->create([
        'status' => 'published',
    ]);

    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
    ]);

    $base = Price::factory()->create([
        'price' => 100,
        'priceable_type' => $variant->getMorphClass(),
        'priceable_id' => $variant->id,
        'currency_id' => $currency->id,
        'min_quantity' => 1,
    ]);

    Price::factory()->create([
        'price' => 50,
        'priceable_type' => $variant->getMorphClass(),
        'priceable_id' => $variant->id,
        'currency_id' => $currency->id,
        'min_quantity' => 10,
    ]);

    Price::factory()->create([
        'price' => 50,
        'priceable_type' => $variant->getMorphClass(),
        'priceable_id' => $variant->id,
        'currency_id' => $currency->id,
        'min_quantity' => 1,
        'customer_group_id' => CustomerGroup::factory(),
    ]);

    $pricing = $manager->for($variant)->get();

    expect($pricing)->toBeInstanceOf(PricingResponse::class);
    expect($pricing->customerGroupPrices)->toHaveCount(0);
    expect($pricing->priceBreaks)->toHaveCount(1);
    expect($pricing->base->id)->toEqual($base->id);
    expect($pricing->matched->id)->toEqual($base->id);
});

test('can get purchasable price with defaults', function () {
    $manager = app(PricingManager::class);

    $currency = Currency::factory()->create([
        'default' => true,
        'exchange_rate' => 1,
    ]);

    $product = Product::factory()->create([
        'status' => 'published',
    ]);

    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
    ]);

    $price = Price::factory()->create([
        'price' => 100,
        'priceable_type' => $variant->getMorphClass(),
        'priceable_id' => $variant->id,
        'currency_id' => $currency->id,
        'min_quantity' => 1,
    ]);

    $pricing = $manager->for($variant)->get();

    expect($pricing)->toBeInstanceOf(PricingResponse::class);

    expect($pricing->matched->id)->toEqual($price->id);
});

test('can fetch customer group price', function () {
    $manager = app(PricingManager::class);

    $customerGroups = CustomerGroup::factory(5)->create();

    $currency = Currency::factory()->create([
        'default' => true,
        'exchange_rate' => 1,
    ]);

    $product = Product::factory()->create([
        'status' => 'published',
    ]);

    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
    ]);

    $base = Price::factory()->create([
        'price' => 100,
        'priceable_type' => $variant->getMorphClass(),
        'priceable_id' => $variant->id,
        'currency_id' => $currency->id,
        'min_quantity' => 1,
    ]);

    $customerGroupPrice = Price::factory()->create([
        'price' => 150,
        'priceable_type' => $variant->getMorphClass(),
        'priceable_id' => $variant->id,
        'currency_id' => $currency->id,
        'min_quantity' => 1,
        'customer_group_id' => $customerGroups->first()->id,
    ]);

    $pricing = $manager->customerGroup($customerGroups->first())
        ->qty(4)->for($variant)->get();

    expect($pricing)->toBeInstanceOf(PricingResponse::class);

    expect($pricing->base->id)->toEqual($base->id);
    expect($pricing->customerGroupPrices)->toHaveCount(1);
    expect($pricing->matched->id)->toEqual($customerGroupPrice->id);

    $pricing = $manager->customerGroup($customerGroups->last())
        ->qty(10)->for($variant)->get();

    expect($pricing->base->id)->toEqual($base->id);
    expect($pricing->customerGroupPrices)->toHaveCount(0);
    expect($pricing->matched->id)->toEqual($base->id);
});

test('can fetch quantity break price', function () {
    $manager = app(PricingManager::class);

    $currency = Currency::factory()->create([
        'default' => true,
        'exchange_rate' => 1,
    ]);

    $product = Product::factory()->create([
        'status' => 'published',
    ]);

    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
    ]);

    $base = Price::factory()->create([
        'price' => 100,
        'priceable_type' => $variant->getMorphClass(),
        'priceable_id' => $variant->id,
        'currency_id' => $currency->id,
        'min_quantity' => 1,
    ]);

    $break10 = Price::factory()->create([
        'price' => 90,
        'priceable_type' => $variant->getMorphClass(),
        'priceable_id' => $variant->id,
        'currency_id' => $currency->id,
        'min_quantity' => 10,
    ]);

    $break20 = Price::factory()->create([
        'price' => 80,
        'priceable_type' => $variant->getMorphClass(),
        'priceable_id' => $variant->id,
        'currency_id' => $currency->id,
        'min_quantity' => 20,
    ]);

    $break30 = Price::factory()->create([
        'price' => 70,
        'priceable_type' => $variant->getMorphClass(),
        'priceable_id' => $variant->id,
        'currency_id' => $currency->id,
        'min_quantity' => 30,
    ]);

    $pricing = $manager->qty(1)->for($variant)->get();

    expect($pricing->base->id)->toEqual($base->id);
    expect($pricing->matched->id)->toEqual($base->id);

    $pricing = $manager->qty(5)->for($variant)->get();

    expect($pricing->base->id)->toEqual($base->id);
    expect($pricing->matched->id)->toEqual($base->id);

    $pricing = $manager->qty(10)->for($variant)->get();

    expect($pricing->base->id)->toEqual($base->id);
    expect($pricing->matched->id)->toEqual($break10->id);

    $pricing = $manager->qty(15)->for($variant)->get();

    expect($pricing->base->id)->toEqual($base->id);
    expect($pricing->matched->id)->toEqual($break10->id);

    $pricing = $manager->qty(20)->for($variant)->get();

    expect($pricing->base->id)->toEqual($base->id);
    expect($pricing->matched->id)->toEqual($break20->id);

    $pricing = $manager->qty(25)->for($variant)->get();

    expect($pricing->base->id)->toEqual($base->id);
    expect($pricing->matched->id)->toEqual($break20->id);

    $pricing = $manager->qty(30)->for($variant)->get();

    expect($pricing->base->id)->toEqual($base->id);
    expect($pricing->matched->id)->toEqual($break30->id);

    $pricing = $manager->qty(100)->for($variant)->get();

    expect($pricing->base->id)->toEqual($base->id);
    expect($pricing->matched->id)->toEqual($break30->id);
});

test('can match based on currency', function () {
    $manager = app(PricingManager::class);

    $defaultCurrency = Currency::factory()->create([
        'default' => true,
        'exchange_rate' => 1,
    ]);

    $secondCurrency = Currency::factory()->create([
        'default' => false,
        'exchange_rate' => 1.2,
    ]);

    $product = Product::factory()->create([
        'status' => 'published',
    ]);

    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
    ]);

    $base = Price::factory()->create([
        'price' => 100,
        'priceable_type' => $variant->getMorphClass(),
        'priceable_id' => $variant->id,
        'currency_id' => $defaultCurrency->id,
        'min_quantity' => 1,
    ]);

    $additional = Price::factory()->create([
        'price' => 120,
        'priceable_type' => $variant->getMorphClass(),
        'priceable_id' => $variant->id,
        'currency_id' => $secondCurrency->id,
        'min_quantity' => 1,
    ]);

    $pricing = $manager->qty(1)->for($variant)->get();

    expect($pricing->base->id)->toEqual($base->id);
    expect($pricing->matched->id)->toEqual($base->id);

    $pricing = $manager->currency($secondCurrency)->qty(1)->for($variant)->get();

    expect($pricing->base->id)->toEqual($additional->id);
    expect($pricing->matched->id)->toEqual($additional->id);
});

/** @test  */
function can_fetch_correct_price_for_user()
{
    $manager = app(PricingManager::class);

    $user = User::factory()->create();

    $customer = Customer::factory()->create();

    $group = CustomerGroup::factory()->create();

    $defaultCurrency = Currency::factory()->create([
        'default' => true,
        'exchange_rate' => 1,
    ]);

    $product = Product::factory()->create([
        'status' => 'published',
    ]);

    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
    ]);

    $base = Price::factory()->create([
        'price' => 100,
        'priceable_type' => $variant->getMorphClass(),
        'priceable_id' => $variant->id,
        'currency_id' => $defaultCurrency->id,
        'min_quantity' => 1,
    ]);

    $groupPrice = Price::factory()->create([
        'price' => 100,
        'priceable_type' => $variant->getMorphClass(),
        'priceable_id' => $variant->id,
        'currency_id' => $defaultCurrency->id,
        'min_quantity' => 1,
        'customer_group_id' => $group->id,
    ]);

    $pricing = $manager->qty(1)->user($user)->for($variant)->get();

    expect($pricing->base->id)->toEqual($base->id);
    expect($pricing->matched->id)->toEqual($base->id);

    $user->customers()->attach($customer);
    $pricing = $manager->qty(1)->user($user->refresh())->for($variant)->get();

    expect($pricing->base->id)->toEqual($base->id);
    expect($pricing->matched->id)->toEqual($base->id);
    expect($pricing->customerGroupPrices)->toHaveCount(0);

    $customer->customerGroups()->attach($group);

    $pricing = $manager->qty(1)->user($user->refresh())->for($variant)->get();

    expect($pricing->base->id)->toEqual($base->id);
    expect($pricing->matched->id)->toEqual($groupPrice->id);
    expect($pricing->customerGroupPrices)->toHaveCount(1);
}

test('can pipeline purchasable price', function () {
    $manager = app(PricingManager::class);

    $currency = Currency::factory()->create([
        'default' => true,
        'exchange_rate' => 1,
    ]);

    $product = Product::factory()->create([
        'status' => 'published',
    ]);

    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
    ]);

    $price = Price::factory()->create([
        'price' => 100,
        'priceable_type' => $variant->getMorphClass(),
        'priceable_id' => $variant->id,
        'currency_id' => $currency->id,
        'min_quantity' => 1,
    ]);

    $pricing = $manager->for($variant)->get();

    expect($pricing)->toBeInstanceOf(PricingResponse::class);

    expect($pricing->matched->id)->toEqual($price->id);
    expect($pricing->matched->price)->toEqual($price->price);

    config()->set('lunar.pricing.pipelines', [
        // set price to 200
        TestPricingPipeline::class,
    ]);

    $pricing = $manager->for($variant)->get();

    expect($pricing)->toBeInstanceOf(PricingResponse::class);

    expect($pricing->matched->id)->toEqual($price->id);

    $this->assertNotEquals($price->price, $pricing->matched->price);
    expect($pricing->matched->price)->toEqual(200);
});

test('using a storefront context applies its currency and customer groups', function () {
    Channel::factory()->create(['default' => true]);
    $currency = Currency::factory()->create(['default' => true, 'exchange_rate' => 1]);
    $group = CustomerGroup::factory()->create(['default' => true]);

    $product = Product::factory()->create(['status' => 'published']);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

    Price::factory()->create([
        'price' => 100,
        'priceable_type' => $variant->getMorphClass(),
        'priceable_id' => $variant->id,
        'currency_id' => $currency->id,
        'min_quantity' => 1,
    ]);

    $groupPrice = Price::factory()->create([
        'price' => 80,
        'priceable_type' => $variant->getMorphClass(),
        'priceable_id' => $variant->id,
        'currency_id' => $currency->id,
        'min_quantity' => 1,
        'customer_group_id' => $group->id,
    ]);

    $context = app(ResolvesStorefrontContext::class)->execute(
        currency: $currency,
        customerGroups: collect([$group]),
    );

    $pricing = app(PricingManager::class)->using($context)->for($variant)->get();

    expect($pricing->matched->id)->toEqual($groupPrice->id);
});

test('a context keeps its customer groups even when an authenticated user has different ones', function () {
    Channel::factory()->create(['default' => true]);
    $currency = Currency::factory()->create(['default' => true, 'exchange_rate' => 1]);
    $contextGroup = CustomerGroup::factory()->create(['default' => true]);
    $userGroup = CustomerGroup::factory()->create(['default' => false]);

    $product = Product::factory()->create(['status' => 'published']);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

    Price::factory()->create([
        'price' => 100,
        'priceable_type' => $variant->getMorphClass(),
        'priceable_id' => $variant->id,
        'currency_id' => $currency->id,
        'min_quantity' => 1,
    ]);

    $contextGroupPrice = Price::factory()->create([
        'price' => 80,
        'priceable_type' => $variant->getMorphClass(),
        'priceable_id' => $variant->id,
        'currency_id' => $currency->id,
        'min_quantity' => 1,
        'customer_group_id' => $contextGroup->id,
    ]);

    Price::factory()->create([
        'price' => 60,
        'priceable_type' => $variant->getMorphClass(),
        'priceable_id' => $variant->id,
        'currency_id' => $currency->id,
        'min_quantity' => 1,
        'customer_group_id' => $userGroup->id,
    ]);

    $user = User::factory()->create();
    $customer = Customer::factory()->create();
    $customer->customerGroups()->attach($userGroup);
    $user->customers()->sync($customer->id);
    $this->actingAs($user);

    $context = app(ResolvesStorefrontContext::class)->execute(
        currency: $currency,
        customerGroups: collect([$contextGroup]),
    );

    $pricing = app(PricingManager::class)->using($context)->for($variant)->get();

    // the context's group price wins; the authenticated user's group is not applied
    expect($pricing->matched->id)->toEqual($contextGroupPrice->id);
});
