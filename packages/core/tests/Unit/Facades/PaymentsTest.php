<?php

namespace Lunar\Tests\Unit\Facades;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Base\PaymentManagerInterface;
use Lunar\Facades\Payments;
use Lunar\Tests\Stubs\TestPaymentDriver;
use Lunar\Tests\TestCase;

/**
 * @group lunar.payments
 */
class PaymentsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function accessor_is_correct()
    {
        $this->assertEquals(PaymentManagerInterface::class, Payments::getFacadeAccessor());
    }

    /** @test */
    public function can_extend_payments()
    {
        Payments::extend('testing', function ($app) {
            return $app->make(TestPaymentDriver::class);
        });

        $this->assertInstanceOf(TestPaymentDriver::class, Payments::driver('testing'));

        $result = Payments::driver('testing')->authorize();

        $this->assertInstanceOf(PaymentAuthorize::class, $result);
    }
}
