<?php

namespace Lunar\Tests\Unit\Base\Casts;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Base\Casts\TaxBreakdown as TaxBreakdownCasts;
use Lunar\Base\ValueObjects\Cart\TaxBreakdown;
use Lunar\Base\ValueObjects\Cart\TaxBreakdownAmount;
use Lunar\DataTypes\Price;
use Lunar\Models\Currency;
use Lunar\Models\Order;
use Lunar\Tests\TestCase;

/**
 * @group model.casts
 */
class TaxBreakdownTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_set_from_value_object()
    {
        $currency = Currency::factory()->create();
        $order = Order::factory()->create();

        $taxBreakdownValueObject = new TaxBreakdown();

        $taxBreakdownValueObject->addAmount(
            new TaxBreakdownAmount(
                price: new Price(100, $currency),
                identifier: 'TAX_AMOUNT_1',
                description: 'Test Tax Breakdown Amount',
                percentage: 20
            )
        );

        $breakDown = new TaxBreakdownCasts;

        $result = $breakDown->set($order, 'tax_breakdown', $taxBreakdownValueObject, []);

        $this->assertArrayHasKey('tax_breakdown', $result);
        $this->assertJson($result['tax_breakdown']);
    }

    /** @test */
    public function can_cast_to_and_from_model()
    {
        $currency = Currency::factory()->create();
        $order = Order::factory()->create();

        $taxBreakdownValueObject = new TaxBreakdown();

        $taxBreakdownValueObject->addAmount(
            new TaxBreakdownAmount(
                price: new Price(100, $currency),
                identifier: 'TAX_AMOUNT_1',
                description: 'Test Tax Breakdown Amount',
                percentage: 20
            )
        );

        $order->update([
            'tax_breakdown' => $taxBreakdownValueObject,
        ]);

        $breakdown = $order->refresh()->tax_breakdown;
        $this->assertInstanceOf(TaxBreakdown::class, $breakdown);
    }
}
