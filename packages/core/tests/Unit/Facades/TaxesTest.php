<?php

namespace Lunar\Tests\Unit\Facades;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Base\TaxManagerInterface;
use Lunar\Base\ValueObjects\Cart\TaxBreakdown;
use Lunar\Facades\Taxes;
use Lunar\Models\Currency;
use Lunar\Models\ProductVariant;
use Lunar\Tests\Stubs\TestTaxDriver;
use Lunar\Tests\TestCase;

/**
 * @group lunar.taxes
 */
class TaxesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function accessor_is_correct()
    {
        $this->assertEquals(TaxManagerInterface::class, Taxes::getFacadeAccessor());
    }

    /** @test */
    public function can_extend_taxes()
    {
        Taxes::extend('testing', function ($app) {
            return $app->make(TestTaxDriver::class);
        });

        $this->assertInstanceOf(TestTaxDriver::class, Taxes::driver('testing'));

        $result = Taxes::driver('testing')->setPurchasable(
            ProductVariant::factory()->create()
        )->setCurrency(
            Currency::factory()->create()
        )->getBreakdown(123);

        $this->assertInstanceOf(TaxBreakdown::class, $result);
    }
}
