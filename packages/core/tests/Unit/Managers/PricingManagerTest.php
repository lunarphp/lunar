<?php

namespace GetCandy\Tests\Unit\Managers;

use GetCandy\Base\DataTransferObjects\PricingResponse;
use GetCandy\Managers\PricingManager;
use GetCandy\Models\Currency;
use GetCandy\Models\Customer;
use GetCandy\Models\CustomerGroup;
use GetCandy\Models\Price;
use GetCandy\Models\Product;
use GetCandy\Models\ProductVariant;
use GetCandy\Tests\Stubs\User;
use GetCandy\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @group getcandy.pricing-manager
 */
class PricingManagerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_initialise_the_manager()
    {
        $this->assertInstanceOf(
            PricingManager::class,
            new PricingManager
        );
    }

    /** @test */
    public function can_set_up_available_guest_pricing()
    {
        $manager = new PricingManager;

        $currency = Currency::factory()->create([
            'default' => true,
            'exchange_rate' => 1,
        ]);

        $product = Product::factory()->create([
            'status' => 'published',
            'brand'  => 'BAR',
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
        ]);

        $base = Price::factory()->create([
            'price' => 100,
            'priceable_type' => ProductVariant::class,
            'priceable_id'   => $variant->id,
            'currency_id'    => $currency->id,
            'tier'           => 1,
        ]);

        Price::factory()->create([
            'price' => 50,
            'priceable_type' => ProductVariant::class,
            'priceable_id'   => $variant->id,
            'currency_id'    => $currency->id,
            'tier'           => 10,
        ]);

        Price::factory()->create([
            'price' => 50,
            'priceable_type' => ProductVariant::class,
            'priceable_id'   => $variant->id,
            'currency_id'    => $currency->id,
            'tier'           => 1,
            'customer_group_id' => CustomerGroup::factory()
        ]);

        $pricing = $manager->for($variant);

        $this->assertInstanceOf(PricingResponse::class, $pricing);
        $this->assertCount(0, $pricing->customerGroupPrices);
        $this->assertCount(1, $pricing->tiered);
        $this->assertEquals($base->id, $pricing->base->id);
        $this->assertEquals($base->id, $pricing->matched->id);
    }

    /** @test */
    public function can_get_purchasable_price_with_defaults()
    {
        $manager = new PricingManager;

        $currency = Currency::factory()->create([
            'default' => true,
            'exchange_rate' => 1,
        ]);

        $product = Product::factory()->create([
            'status' => 'published',
            'brand'  => 'BAR',
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
        ]);


        $price = Price::factory()->create([
            'price' => 100,
            'priceable_type' => ProductVariant::class,
            'priceable_id'   => $variant->id,
            'currency_id'    => $currency->id,
            'tier'           => 1,
        ]);

        $pricing = $manager->for($variant);

        $this->assertInstanceOf(PricingResponse::class, $pricing);

        $this->assertEquals($price->id, $pricing->matched->id);
    }

    /**  @test */
    public function can_fetch_customer_group_price()
    {
        $manager = new PricingManager;

        $customerGroups = CustomerGroup::factory(5)->create();

        $currency = Currency::factory()->create([
            'default' => true,
            'exchange_rate' => 1,
        ]);

        $product = Product::factory()->create([
            'status' => 'published',
            'brand'  => 'BAR',
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
        ]);

        $base = Price::factory()->create([
            'price' => 100,
            'priceable_type' => ProductVariant::class,
            'priceable_id'   => $variant->id,
            'currency_id'    => $currency->id,
            'tier'           => 1,
        ]);

        $customerGroupPrice = Price::factory()->create([
            'price' => 150,
            'priceable_type' => ProductVariant::class,
            'priceable_id'   => $variant->id,
            'currency_id'    => $currency->id,
            'tier'           => 1,
            'customer_group_id' => $customerGroups->first()->id,
        ]);

        $pricing = $manager->customerGroup($customerGroups->first())
            ->qty(4)->for($variant);

        $this->assertInstanceOf(PricingResponse::class, $pricing);

        $this->assertEquals($base->id, $pricing->base->id);
        $this->assertCount(1, $pricing->customerGroupPrices);
        $this->assertEquals($customerGroupPrice->id, $pricing->matched->id);

        $pricing = $manager->customerGroup($customerGroups->last())
            ->qty(10)->for($variant);

        $this->assertEquals($base->id, $pricing->base->id);
        $this->assertCount(0, $pricing->customerGroupPrices);
        $this->assertEquals($base->id, $pricing->matched->id);
    }

    /** @test */
    public function can_fetch_tiered_price()
    {
        $manager = new PricingManager;

        $currency = Currency::factory()->create([
            'default' => true,
            'exchange_rate' => 1,
        ]);

        $product = Product::factory()->create([
            'status' => 'published',
            'brand'  => 'BAR',
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
        ]);

        $base = Price::factory()->create([
            'price' => 100,
            'priceable_type' => ProductVariant::class,
            'priceable_id'   => $variant->id,
            'currency_id'    => $currency->id,
            'tier'           => 1,
        ]);

        $tiered10 = Price::factory()->create([
            'price' => 90,
            'priceable_type' => ProductVariant::class,
            'priceable_id'   => $variant->id,
            'currency_id'    => $currency->id,
            'tier'           => 10,
        ]);

        $tiered20 = Price::factory()->create([
            'price' => 80,
            'priceable_type' => ProductVariant::class,
            'priceable_id'   => $variant->id,
            'currency_id'    => $currency->id,
            'tier'           => 20,
        ]);

        $tiered30 = Price::factory()->create([
            'price' => 70,
            'priceable_type' => ProductVariant::class,
            'priceable_id'   => $variant->id,
            'currency_id'    => $currency->id,
            'tier'           => 30,
        ]);

        $pricing = $manager->qty(1)->for($variant);

        $this->assertEquals($base->id, $pricing->base->id);
        $this->assertEquals($base->id, $pricing->matched->id);

        $pricing = $manager->qty(5)->for($variant);

        $this->assertEquals($base->id, $pricing->base->id);
        $this->assertEquals($base->id, $pricing->matched->id);

        $pricing = $manager->qty(10)->for($variant);

        $this->assertEquals($base->id, $pricing->base->id);
        $this->assertEquals($tiered10->id, $pricing->matched->id);

        $pricing = $manager->qty(15)->for($variant);

        $this->assertEquals($base->id, $pricing->base->id);
        $this->assertEquals($tiered10->id, $pricing->matched->id);

        $pricing = $manager->qty(20)->for($variant);

        $this->assertEquals($base->id, $pricing->base->id);
        $this->assertEquals($tiered20->id, $pricing->matched->id);

        $pricing = $manager->qty(25)->for($variant);

        $this->assertEquals($base->id, $pricing->base->id);
        $this->assertEquals($tiered20->id, $pricing->matched->id);

        $pricing = $manager->qty(30)->for($variant);

        $this->assertEquals($base->id, $pricing->base->id);
        $this->assertEquals($tiered30->id, $pricing->matched->id);

        $pricing = $manager->qty(100)->for($variant);

        $this->assertEquals($base->id, $pricing->base->id);
        $this->assertEquals($tiered30->id, $pricing->matched->id);
    }

    /** @test */
    public function can_match_based_on_currency()
    {
        $manager = new PricingManager;

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
            'brand'  => 'BAR',
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
        ]);

        $base = Price::factory()->create([
            'price' => 100,
            'priceable_type' => ProductVariant::class,
            'priceable_id'   => $variant->id,
            'currency_id'    => $defaultCurrency->id,
            'tier'           => 1,
        ]);

        $additional = Price::factory()->create([
            'price' => 120,
            'priceable_type' => ProductVariant::class,
            'priceable_id'   => $variant->id,
            'currency_id'    => $secondCurrency->id,
            'tier'           => 1,
        ]);

        $pricing = $manager->qty(1)->for($variant);

        $this->assertEquals($base->id, $pricing->base->id);
        $this->assertEquals($base->id, $pricing->matched->id);

        $pricing = $manager->currency($secondCurrency)->qty(1)->for($variant);

        $this->assertEquals($additional->id, $pricing->base->id);
        $this->assertEquals($additional->id, $pricing->matched->id);
    }

    /** @test  */
    public function can_fetch_correct_price_for_user()
    {
        $manager = new PricingManager;

        $user = User::factory()->create();

        $customer = Customer::factory()->create();

        $group = CustomerGroup::factory()->create();

        $defaultCurrency = Currency::factory()->create([
            'default' => true,
            'exchange_rate' => 1,
        ]);

        $product = Product::factory()->create([
            'status' => 'published',
            'brand'  => 'BAR',
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
        ]);

        $base = Price::factory()->create([
            'price' => 100,
            'priceable_type' => ProductVariant::class,
            'priceable_id'   => $variant->id,
            'currency_id'    => $defaultCurrency->id,
            'tier'           => 1,
        ]);

        $groupPrice = Price::factory()->create([
            'price' => 100,
            'priceable_type' => ProductVariant::class,
            'priceable_id'   => $variant->id,
            'currency_id'    => $defaultCurrency->id,
            'tier'           => 1,
            'customer_group_id' => $group->id,
        ]);

        $pricing = $manager->qty(1)->user($user)->for($variant);

        $this->assertEquals($base->id, $pricing->base->id);
        $this->assertEquals($base->id, $pricing->matched->id);

        $user->customers()->attach($customer);
        $pricing = $manager->qty(1)->user($user->refresh())->for($variant);

        $this->assertEquals($base->id, $pricing->base->id);
        $this->assertEquals($base->id, $pricing->matched->id);
        $this->assertCount(0, $pricing->customerGroupPrices);

        $customer->customerGroups()->attach($group);

        $pricing = $manager->qty(1)->user($user->refresh())->for($variant);

        $this->assertEquals($base->id, $pricing->base->id);
        $this->assertEquals($groupPrice->id, $pricing->matched->id);
        $this->assertCount(1, $pricing->customerGroupPrices);
    }
}
