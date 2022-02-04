<?php

namespace GetCandy\Tests\Unit\Facades;

use GetCandy\Base\TaxManagerInterface;
use GetCandy\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use GetCandy\Facades\Taxes;
use GetCandy\Tests\Stubs\TestTaxDriver;

/**
 * @group getcandy.taxes
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

        $this->assertIsIterable($result);
    }
}
