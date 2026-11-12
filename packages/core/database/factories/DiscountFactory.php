<?php

namespace Lunar\Core\Database\Factories;

use Illuminate\Support\Str;
use Lunar\Core\DiscountTypes\PercentageOff;
use Lunar\Core\Enums\DiscountRedemptionMethod;
use Lunar\Core\Models\Coupon;
use Lunar\Core\Models\Discount;

class DiscountFactory extends BaseFactory
{
    protected $model = Discount::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->name;

        return [
            'public_id' => (string) Str::ulid(),
            'name' => $name,
            'handle' => Str::snake($name),
            'type' => PercentageOff::class,
            'redemption' => DiscountRedemptionMethod::Automatic,
            'starts_at' => now(),
        ];
    }

    /**
     * A discount redeemed with a coupon, with one code when given.
     */
    public function coupon(?string $code = null, array $attributes = []): static
    {
        $factory = $this->state(['redemption' => DiscountRedemptionMethod::Coupon]);

        if ($code === null) {
            return $factory;
        }

        return $factory->has(Coupon::factory()->state(['code' => $code, ...$attributes]), 'coupons');
    }
}
