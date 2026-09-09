<?php

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Lunar\Core\Events\Orders\OrderPaymentStatusUpdated;
use Lunar\Core\Notifications\OrderConfirmation;
use Lunar\Core\Notifications\PaymentReceived;
use Lunar\Paypal\PaypalPaymentType;
use Lunar\Tests\Paypal\Unit\TestCase;
use Lunar\Tests\Paypal\Utils\CartBuilder;
use Lunar\Tests\Paypal\Utils\PaypalFake;

uses(TestCase::class);

it('records the capture before placing the order, so checkout sends the confirmation only', function () {
    NotificationFacade::fake();

    // Every payment-status rollup during checkout must see an unplaced order:
    // that is what keeps the `paid` email quiet and the confirmation singular.
    $placedAtRollup = [];
    Event::listen(OrderPaymentStatusUpdated::class, function (OrderPaymentStatusUpdated $event) use (&$placedAtRollup) {
        $placedAtRollup[] = $event->order->isPlaced();
    });

    $cart = CartBuilder::build()->calculate();
    PaypalFake::forCart($cart);

    (new PaypalPaymentType)->cart($cart)->withData([
        'paypal_order_id' => '5O190127TN364715T',
    ])->authorize();

    $order = $cart->refresh()->completedOrder;

    expect($order->placed_at)->not->toBeNull()
        ->and($order->transactions()->whereType('capture')->where('success', true)->exists())->toBeTrue()
        ->and($placedAtRollup)->not->toBeEmpty()
        ->and(array_unique($placedAtRollup))->toBe([false]);

    NotificationFacade::assertSentToTimes($order, OrderConfirmation::class, 1);
    NotificationFacade::assertNotSentTo($order, PaymentReceived::class);
});
