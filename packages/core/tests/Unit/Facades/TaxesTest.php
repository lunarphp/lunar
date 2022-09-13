<?php

namespace Lunar\Tests\Unit\Facades;

use Lunar\Base\DataTransferObjects\TaxBreakdown;
use Lunar\Base\TaxManagerInterface;
use Lunar\Facades\Taxes;
use Lunar\Tests\Stubs\TestTaxDriver;
use Lunar\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

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

        $result = Taxes::driver('testing')->getBreakdown(123);

        $this->assertInstanceOf(TaxBreakdown::class, $result);
    }
}
