<?php

namespace Lunar\Tests\Unit\Managers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Lunar\Base\DataTransferObjects\CartDiscount;
use Lunar\Base\DiscountManagerInterface;
use Lunar\DiscountTypes\Discount as DiscountTypesDiscount;
use Lunar\Managers\DiscountManager;
use Lunar\Models\CartLine;
use Lunar\Models\Channel;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Discount;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;
use Lunar\Tests\Stubs\TestDiscountType;
use Lunar\Tests\TestCase;

/**
 * @group lunar.discounts
 * @group lunar.discounts.managers
 */
class DiscountManagerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function can_instantiate_manager()
    {
        $manager = app(DiscountManagerInterface::class);
        $this->assertInstanceOf(DiscountManager::class, $manager);
    }

    /** @test */
    public function can_set_channel()
    {
        $manager = app(DiscountManagerInterface::class);

        $channel = Channel::factory()->create();

        $this->assertCount(0, $manager->getChannels());

        $manager->channel($channel);

        $this->assertCount(1, $manager->getChannels());

        $channels = Channel::factory(2)->create();

        $manager->channel($channels);

        $this->assertCount(2, $manager->getChannels());

        $this->expectException(InvalidArgumentException::class);

        $manager->channel(Product::factory(2)->create());
    }

    /** @test */
    public function can_set_customer_group()
    {
        $manager = app(DiscountManagerInterface::class);

        $customerGroup = CustomerGroup::factory()->create();

        $this->assertCount(0, $manager->getCustomerGroups());

        $manager->customerGroup($customerGroup);

        $this->assertCount(1, $manager->getCustomerGroups());

        $customerGroups = CustomerGroup::factory(2)->create();

        $manager->customerGroup($customerGroups);

        $this->assertCount(2, $manager->getCustomerGroups());

        $this->expectException(InvalidArgumentException::class);

        $manager->channel(Product::factory(2)->create());
    }

    /** @test */
    public function can_restrict_discounts_to_channel()
    {
        $channel = Channel::factory()->create([
            'default' => true,
        ]);

        $channelTwo = Channel::factory()->create([
            'default' => false,
        ]);

        $customerGroup = CustomerGroup::factory()->create([
            'default' => true,
        ]);

        $discount = Discount::factory()->create();

        $manager = app(DiscountManagerInterface::class);

        $this->assertEmpty($manager->getDiscounts());

        $discount->customerGroups()->sync([
            $customerGroup->id => [
                'enabled' => true,
                'visible' => true,
                'starts_at' => now(),
            ],
        ]);

        $discount->channels()->sync([
            $channel->id => [
                'enabled' => true,
                'starts_at' => now(),
            ],
            $channelTwo->id => [
                'enabled' => false,
                'starts_at' => now(),
            ],
        ]);

        $this->assertCount(1, $manager->getDiscounts());

        $discount->channels()->sync([
            $channel->id => [
                'enabled' => true,
                'starts_at' => now()->addHour(),
            ],
            $channelTwo->id => [
                'enabled' => false,
                'starts_at' => now(),
            ],
        ]);

        $this->assertEmpty($manager->getDiscounts());

        $discount->channels()->sync([
            $channel->id => [
                'enabled' => true,
                'starts_at' => now()->subDay(),
                'ends_at' => now(),
            ],
            $channelTwo->id => [
                'enabled' => true,
                'starts_at' => now(),
            ],
        ]);

        $this->assertEmpty($manager->getDiscounts());

        $manager->channel($channelTwo);

        $this->assertCount(1, $manager->getDiscounts());
    }

    /** @test */
    public function can_restrict_discounts_to_customer_group()
    {
        $channel = Channel::factory()->create([
            'default' => true,
        ]);

        $customerGroup = CustomerGroup::factory()->create([
            'default' => true,
        ]);

        $customerGroupTwo = CustomerGroup::factory()->create([
            'default' => false,
        ]);

        $discount = Discount::factory()->create();

        $discount->channels()->sync([
            $channel->id => [
                'enabled' => true,
                'starts_at' => now(),
            ],
        ]);

        $discount->customerGroups()->sync([
            $customerGroup->id => [
                'enabled' => true,
                'visible' => true,
                'starts_at' => now(),
            ],
        ]);

        $manager = app(DiscountManagerInterface::class);

        $this->assertCount(1, $manager->getDiscounts());

        $discount->customerGroups()->sync([
            $channel->id => [
                'enabled' => false,
                'starts_at' => now(),
            ],
        ]);

        $this->assertEmpty($manager->getDiscounts());

        $discount->customerGroups()->sync([
            $customerGroup->id => [
                'enabled' => true,
                'visible' => true,
                'starts_at' => now()->addMinutes(1),
            ],
            $customerGroupTwo->id => [
                'enabled' => true,
                'visible' => false,
                'starts_at' => now()->addMinutes(1),
            ],
        ]);

        $manager->customerGroup($customerGroupTwo);

        $this->assertEmpty($manager->getDiscounts());
    }

    /**
     * @test
     */
    public function can_fetch_discount_types()
    {
        $manager = app(DiscountManagerInterface::class);

        $this->assertInstanceOf(Collection::class, $manager->getTypes());
    }

    /**
     * @test
     */
    public function can_fetch_applied_discounts()
    {
        $manager = app(DiscountManagerInterface::class);

        $this->assertInstanceOf(Collection::class, $manager->getApplied());
        $this->assertCount(0, $manager->getApplied());
    }

    /**
     * @test
     */
    public function can_add_applied_discount()
    {
        $manager = app(DiscountManagerInterface::class);

        $this->assertInstanceOf(Collection::class, $manager->getApplied());

        $this->assertCount(0, $manager->getApplied());

        ProductVariant::factory()->create();

        $discount = Discount::factory()->create();
        $cartLine = CartLine::factory()->create();

        $discount = new CartDiscount(
            model: $cartLine,
            discount: $discount
        );

        $manager->addApplied($discount);

        $this->assertCount(1, $manager->getApplied());
    }

    /**
     * @test
     */
    public function can_add_new_types()
    {
        $manager = app(DiscountManagerInterface::class);

        $testType = $manager->getTypes()->first(function ($type) {
            return get_class($type) == TestDiscountType::class;
        });

        $this->assertNull($testType);

        $manager->addType(TestDiscountType::class);

        $testType = $manager->getTypes()->first(function ($type) {
            return get_class($type) == TestDiscountType::class;
        });

        $this->assertInstanceOf(TestDiscountType::class, $testType);
    }

    /** @test */
    public function can_validate_coupons()
    {
        $manager = app(DiscountManagerInterface::class);

        Discount::factory()->create([
            'type' => DiscountTypesDiscount::class,
            'name' => 'Test Coupon',
            'coupon' => '10OFF',
            'data' => [
                'fixed_value' => false,
                'percentage' => 10,
            ],
        ]);

        $this->assertTrue(
            $manager->validateCoupon('10OFF')
        );

        $this->assertFalse(
            $manager->validateCoupon('20OFF')
        );
    }
}
