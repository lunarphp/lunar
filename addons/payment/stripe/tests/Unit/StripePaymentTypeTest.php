<?php

namespace Lunar\Stripe\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Models\Transaction;
use Lunar\Stripe\Facades\StripeFacade;
use Lunar\Stripe\StripePaymentType;
use Lunar\Stripe\Tests\TestCase;
use Lunar\Stripe\Tests\Utils\CartBuilder;

/**
 * @group stripe.payments
 */
class StripePaymentTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_order_is_captured()
    {
        $cart = CartBuilder::build();

        $payment = new StripePaymentType;

        $response = $payment->cart($cart)->withData([
            'payment_intent' => 'PI_CAPTURE',
        ])->authorize();

        $this->assertInstanceOf(PaymentAuthorize::class, $response);
        $this->assertTrue($response->success);
        $this->assertNotNull($cart->refresh()->order->placed_at);

        $this->assertEquals('PI_CAPTURE', $cart->meta->payment_intent);

        $this->assertDatabaseHas((new Transaction)->getTable(), [
            'order_id' => $cart->refresh()->order->id,
            'type' => 'capture',
        ]);
    }

    /**
     * @group thisone
     */
    public function test_handle_failed_payment()
    {
        $cart = CartBuilder::build();

        $payment = new StripePaymentType;

        $response = $payment->cart($cart)->withData([
            'payment_intent' => 'PI_FAIL',
        ])->authorize();

        $this->assertInstanceOf(PaymentAuthorize::class, $response);
        $this->assertFalse($response->success);
        $this->assertNull($cart->refresh()->order->placed_at);

        $this->assertDatabaseMissing((new Transaction)->getTable(), [
            'order_id' => $cart->refresh()->order->id,
            'type' => 'capture',
        ]);
    }

    public function test_existing_intent_is_returned_if_it_exists()
    {
        $cart = CartBuilder::build([
            'meta' => [
                'payment_intent' => 'PI_FOOBAR',
            ],
        ]);

        StripeFacade::createIntent($cart->getManager()->getCart());

        $this->assertEquals(
            $cart->refresh()->meta->payment_intent,
            'PI_FOOBAR'
        );
    }
}
