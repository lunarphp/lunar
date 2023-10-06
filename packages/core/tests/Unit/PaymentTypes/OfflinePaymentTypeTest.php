<?php

namespace Lunar\Tests\Unit\PaymentTypes;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Facades\Payments;
use Lunar\Models\Cart;
use Lunar\Models\CartAddress;
use Lunar\Models\Country;
use Lunar\Models\Order;
use Lunar\Tests\TestCase;

/**
 * @group lunar.payment-types
 */
class OfflinePaymentTypeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_authorize_payment()
    {
        $cart = Cart::factory()->create();

        Config::set('lunar.payments.types.offline', [
            'authorized' => 'offline-payment',
        ]);

        CartAddress::factory()->create([
            'cart_id' => $cart->id,
            'type' => 'billing',
            'country_id' => Country::factory(),
            'first_name' => 'Santa',
            'line_one' => '123 Elf Road',
            'city' => 'Lapland',
            'postcode' => 'BILL',
        ]);

        CartAddress::factory()->create([
            'cart_id' => $cart->id,
            'type' => 'shipping',
            'country_id' => Country::factory(),
            'first_name' => 'Santa',
            'line_one' => '123 Elf Road',
            'city' => 'Lapland',
            'postcode' => 'SHIPP',
        ]);

        $result = Payments::driver('offline')->cart($cart->refresh())->authorize();

        $this->assertInstanceOf(PaymentAuthorize::class, $result);
        $this->assertTrue($result->success);

        $this->assertInstanceOf(Order::class, $cart->refresh()->completedOrder);
    }

    /** @test */
    public function can_override_status()
    {
        $cart = Cart::factory()->create();

        Config::set('lunar.payments.types.offline', [
            'authorized' => 'offline-payment',
        ]);

        CartAddress::factory()->create([
            'cart_id' => $cart->id,
            'type' => 'billing',
            'country_id' => Country::factory(),
            'first_name' => 'Santa',
            'line_one' => '123 Elf Road',
            'city' => 'Lapland',
            'postcode' => 'BILL',
        ]);

        CartAddress::factory()->create([
            'cart_id' => $cart->id,
            'type' => 'shipping',
            'country_id' => Country::factory(),
            'first_name' => 'Santa',
            'line_one' => '123 Elf Road',
            'city' => 'Lapland',
            'postcode' => 'SHIPP',
        ]);

        Payments::driver('offline')->cart($cart->refresh())->withData([
            'authorized' => 'custom-status',
        ])->authorize();

        $order = $cart->refresh()->completedOrder;

        $this->assertSame('custom-status', $order->status);
    }

    /** @test */
    public function can_set_additional_meta()
    {
        $cart = Cart::factory()->create();

        Config::set('lunar.payments.types.offline', [
            'authorized' => 'offline-payment',
        ]);

        CartAddress::factory()->create([
            'cart_id' => $cart->id,
            'type' => 'billing',
            'country_id' => Country::factory(),
            'first_name' => 'Santa',
            'line_one' => '123 Elf Road',
            'city' => 'Lapland',
            'postcode' => 'BILL',
        ]);

        CartAddress::factory()->create([
            'cart_id' => $cart->id,
            'type' => 'shipping',
            'country_id' => Country::factory(),
            'first_name' => 'Santa',
            'line_one' => '123 Elf Road',
            'city' => 'Lapland',
            'postcode' => 'SHIPP',
        ]);

        Payments::driver('offline')->cart($cart->refresh())->withData([
            'meta' => [
                'foo' => 'bar',
            ],
        ])->authorize();

        $order = $cart->refresh()->completedOrder;

        $meta = (array) $order->meta;

        $this->assertEquals('bar', $meta['foo']);
    }
}
