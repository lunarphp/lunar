<?php

namespace GetCandy\Tests\Unit\Managers;

use GetCandy\Base\DataTransferObjects\CartDiscount;
use GetCandy\Base\DiscountManagerInterface;
use GetCandy\Managers\DiscountManager;
use GetCandy\Models\CartLine;
use GetCandy\Models\Discount;
use GetCandy\Models\ProductVariant;
use GetCandy\Tests\Stubs\TestDiscountType;
use GetCandy\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;

/**
 * @group getcandy.discounts
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
            cartLine: $cartLine,
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
}
