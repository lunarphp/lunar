<?php

namespace Lunar\Tests\Unit\Base\Validation;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Base\Validation\CouponValidator;
use Lunar\DiscountTypes\Coupon;
use Lunar\Models\Discount;
use Lunar\Tests\TestCase;

/**
 * @group getcandy.discounts.validators
 */
class CouponValidatorTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_validate_coupons()
    {
        $validator = app(CouponValidator::class);

        Discount::factory()->create([
            'type' => Coupon::class,
            'name' => 'Test Coupon',
            'data' => [
                'coupon' => '10OFF',
                'fixed_value' => false,
                'percentage' => 10,
            ],
        ]);

        $this->assertTrue(
            $validator->validate('10OFF')
        );

        $this->assertTrue(
            $validator->validate('10off')
        );

        $this->assertTrue(
            $validator->validate('10oFf')
        );

        $this->assertFalse(
            $validator->validate('20OFF')
        );
    }

    /** @test **/
    public function can_validate_based_on_uses()
    {
        $validator = app(CouponValidator::class);

        $discount = Discount::factory()->create([
            'type' => Coupon::class,
            'name' => 'Test Coupon',
            'uses' => 10,
            'max_uses' => 20,
            'data' => [
                'coupon' => '10OFF',
                'fixed_value' => false,
                'percentage' => 10,
            ],
        ]);

        $this->assertTrue(
            $validator->validate('10OFF')
        );

        $discount->update([
            'uses' => 20,
        ]);

        $this->assertFalse(
            $validator->validate('10OFF')
        );

        $discount->update([
            'max_uses' => null,
        ]);

        $this->assertTrue(
            $validator->validate('10OFF')
        );
    }

    /** @test */
    public function can_validate_based_on_start_and_end_dates()
    {
        $validator = app(CouponValidator::class);

        $discount = Discount::factory()->create([
            'type' => Coupon::class,
            'name' => 'Test Coupon',
            'uses' => 0,
            'max_uses' => null,
            'starts_at' => now()->startOfDay(),
            'ends_at' => now()->endOfWeek(),
            'data' => [
                'coupon' => '10OFF',
                'fixed_value' => false,
                'percentage' => 10,
            ],
        ]);

        $this->assertTrue(
            $validator->validate('10OFF')
        );

        $discount->update([
            'starts_at' => now()->subWeek(),
            'ends_at' => now()->subWeek()->endOfWeek(),
        ]);

        $this->assertFalse(
            $validator->validate('10OFF')
        );

        $discount->update([
            'starts_at' => now()->subWeek(),
            'ends_at' => now()->subWeek()->endOfWeek(),
        ]);

        $this->assertFalse(
            $validator->validate('10OFF')
        );
    }
}
