<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Lunar\Core\DataObjects\RefundRequest;
use Lunar\Core\Facades\OrderNotifications;
use Lunar\Core\Facades\Payments;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\Models\Transaction;
use Lunar\Core\Notifications\OrderCancellation;
use Lunar\Core\Notifications\OrderConfirmation;
use Lunar\Core\Notifications\OrderProvisioned;
use Lunar\Core\Notifications\OrderReadyForCollection;
use Lunar\Core\Notifications\OrderShipped;
use Lunar\Core\Notifications\PaymentReceived;
use Lunar\Core\Notifications\RefundIssued;
use Lunar\Core\Notifications\ReturnReceived;
use Lunar\Core\States\Fulfilment\ReadyForCollection;
use Lunar\Tests\Core\Stubs\TestPaymentDriver;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
    Currency::factory()->create(['default' => true, 'code' => 'GBP', 'decimal_places' => 2]);
    Location::factory()->default()->create();

    NotificationFacade::fake();
});

function notificationDraftOrder(string $type = 'physical'): Order
{
    $order = Order::factory()->create();
    OrderLine::factory()->for($order)->create(['type' => $type, 'quantity' => 1, 'requires_fulfilment' => true]);

    return $order;
}

function notificationCapture(Order $order): Transaction
{
    return $order->transactions()->create([
        'type' => 'capture', 'success' => true, 'amount' => $order->total,
        'driver' => 'testing', 'reference' => uniqid(), 'status' => 'settled',
    ]);
}

// ------------------------------------------------------------------- placement

test('placing an order sends the confirmation once', function () {
    $order = notificationDraftOrder();
    $order->update(['placed_at' => now()]);

    NotificationFacade::assertSentToTimes($order->fresh(), OrderConfirmation::class, 1);
});

test('creating a draft order sends nothing', function () {
    Order::factory()->create();

    NotificationFacade::assertNothingSent();
});

test('a card checkout that captures then places sends the confirmation only', function () {
    // Drivers record the capture first: the paid rollup fires on an unplaced
    // order and is skipped, then placement sends the confirmation.
    $order = notificationDraftOrder();
    notificationCapture($order);
    $order->fresh()->update(['placed_at' => now()]);

    NotificationFacade::assertSentToTimes($order->fresh(), OrderConfirmation::class, 1);
    NotificationFacade::assertNotSentTo($order->fresh(), PaymentReceived::class);
});

test('a capture on an already placed order sends the payment received email', function () {
    $order = notificationDraftOrder();
    $order->update(['placed_at' => now()]);
    notificationCapture($order->fresh());

    NotificationFacade::assertSentToTimes($order->fresh(), PaymentReceived::class, 1);
});

test('forgetting a default switches it off', function () {
    OrderNotifications::forget('payment-received');

    $order = notificationDraftOrder();
    $order->update(['placed_at' => now()]);
    notificationCapture($order->fresh());

    NotificationFacade::assertNotSentTo($order->fresh(), PaymentReceived::class);
});

// ------------------------------------------------------------------ fulfilment

test('shipping sends the shipped email carrying the fulfilment, and a quiet ship does not', function () {
    $order = notificationDraftOrder();
    $fulfilment = $order->createFulfilment([$order->lines()->first()->id => 1])->ship();

    NotificationFacade::assertSentTo(
        $order->fresh(),
        OrderShipped::class,
        fn (OrderShipped $notification) => $notification->fulfilment->is($fulfilment),
    );

    $quiet = notificationDraftOrder();
    $quiet->createFulfilment([$quiet->lines()->first()->id => 1])->ship(notify: false);

    NotificationFacade::assertNotSentTo($quiet->fresh(), OrderShipped::class);
});

test('a collection fulfilment reaching ready-for-collection sends the collection email', function () {
    $order = notificationDraftOrder();
    $fulfilment = $order->createFulfilment([$order->lines()->first()->id => 1], ['method' => 'collection']);

    $fulfilment->transition(ReadyForCollection::class);

    NotificationFacade::assertSentToTimes($order->fresh(), OrderReadyForCollection::class, 1);

    // Collecting is a counter handover: no email.
    $fulfilment->fresh()->fulfil();

    NotificationFacade::assertSentToTimes($order->fresh(), OrderReadyForCollection::class, 1);
    NotificationFacade::assertNotSentTo($order->fresh(), OrderShipped::class);
});

test('provisioning a digital fulfilment sends the provisioned email', function () {
    $order = notificationDraftOrder('digital');
    $order->createFulfilment([$order->lines()->first()->id => 1], ['method' => 'digital'])->fulfil();

    NotificationFacade::assertSentToTimes($order->fresh(), OrderProvisioned::class, 1);
    NotificationFacade::assertNotSentTo($order->fresh(), OrderShipped::class);
});

test('marking a fulfilment returned sends the return received email', function () {
    $order = notificationDraftOrder();
    $order->createFulfilment([$order->lines()->first()->id => 1])->ship()->markReturned();

    NotificationFacade::assertSentToTimes($order->fresh(), ReturnReceived::class, 1);
});

// --------------------------------------------------------- cancellation, refund

test('cancelling with notify sends the cancellation email', function () {
    $order = notificationDraftOrder();
    $order->cancel(notify: true);

    NotificationFacade::assertSentToTimes($order->fresh(), OrderCancellation::class, 1);
});

test('a refund with notify sends the refund email exactly once, even when the order becomes fully refunded', function () {
    Payments::extend('testing', fn ($app) => $app->make(TestPaymentDriver::class));

    $order = notificationDraftOrder();
    $order->update(['placed_at' => now()]);
    $capture = notificationCapture($order->fresh());

    $order->fresh()->refund(new RefundRequest(transactionId: $capture->id, adjustment: (string) ($order->total / 100)));

    expect((string) $order->fresh()->payment_status)->toBe('refunded');
    NotificationFacade::assertSentToTimes($order->fresh(), RefundIssued::class, 1);
});

test('a quiet refund sends nothing', function () {
    Payments::extend('testing', fn ($app) => $app->make(TestPaymentDriver::class));

    $order = notificationDraftOrder();
    $order->update(['placed_at' => now()]);
    $capture = notificationCapture($order->fresh());

    $order->fresh()->refund(new RefundRequest(transactionId: $capture->id, adjustment: '1.00', notify: false));

    NotificationFacade::assertNotSentTo($order->fresh(), RefundIssued::class);
});
