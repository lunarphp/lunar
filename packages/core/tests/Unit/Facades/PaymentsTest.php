<?php

namespace GetCandy\Tests\Unit\Facades;

use GetCandy\Base\DataTransferObjects\PaymentAuthorize;
use GetCandy\Base\PaymentManagerInterface;
use GetCandy\Facades\Payments;
use GetCandy\Tests\Stubs\TestPaymentDriver;
use GetCandy\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @group getcandy.payments
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
