<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Notification;
use Lunar\Core\Contracts\Notifications\AcceptsCustomerMessage;
use Lunar\Core\Facades\Carriers;
use Lunar\Core\Facades\OrderNotifications;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderAddress;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\Models\Transaction;
use Lunar\Core\Notifications\OrderCancellation;
use Lunar\Core\Notifications\OrderConfirmation;
use Lunar\Core\Notifications\OrderProvisioned;
use Lunar\Core\Notifications\OrderReadyForCollection;
use Lunar\Core\Notifications\OrderShipped;
use Lunar\Core\Notifications\PartialFulfilmentUpdate;
use Lunar\Core\Notifications\PaymentReceived;
use Lunar\Core\Notifications\RefundIssued;
use Lunar\Core\Notifications\ReturnReceived;
use Lunar\Core\States\Order\Payment\Paid;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
    Currency::factory()->create(['default' => true, 'code' => 'GBP', 'decimal_places' => 2]);
    Location::factory()->default()->create(['name' => 'Camden store']);
});

function notificationOrder(): Order
{
    $order = Order::factory()->create([
        'reference' => 'ORD-1',
        'placed_at' => now(),
        'sub_total' => 15000,
        'discount_total' => 1000,
        'shipping_total' => 500,
        'tax_total' => 2900,
        'total' => 17400,
        'payment_status' => Paid::class,
    ]);

    OrderLine::factory()->for($order)->create(['type' => 'physical', 'description' => 'Widget', 'option' => 'Blue', 'quantity' => 2, 'total' => 10000]);
    OrderLine::factory()->for($order)->create(['type' => 'physical', 'description' => 'Gadget', 'option' => null, 'quantity' => 1, 'total' => 5000]);
    OrderLine::factory()->for($order)->create(['type' => 'shipping', 'description' => 'Standard delivery', 'quantity' => 1, 'total' => 500, 'requires_shipping' => false, 'requires_fulfilment' => false]);

    $country = Country::factory()->create(['name' => 'United Kingdom']);

    $order->addresses()->create(OrderAddress::factory()->raw([
        'type' => 'billing', 'first_name' => 'Ada', 'last_name' => 'Lovelace', 'company_name' => null,
        'line_one' => '1 Analytical Way', 'line_two' => null, 'line_three' => null, 'city' => 'London', 'state' => null,
        'postcode' => 'E1 6AN', 'country_id' => $country->id, 'contact_email' => 'ada@example.com',
    ]));
    $order->addresses()->create(OrderAddress::factory()->raw([
        'type' => 'shipping', 'first_name' => 'Ada', 'last_name' => 'Lovelace', 'company_name' => 'Difference Engines Ltd',
        'line_one' => '2 Engine Street', 'line_two' => null, 'line_three' => null, 'city' => 'Manchester', 'state' => null,
        'postcode' => 'M1 1AA', 'country_id' => $country->id, 'contact_email' => null,
    ]));

    return $order->fresh();
}

function renderNotification(Notification $notification): string
{
    $mail = $notification->toMail(new AnonymousNotifiable);

    return (string) $mail->render();
}

test('the order confirmation renders the reference, lines, totals, addresses, payment state and message', function () {
    $order = notificationOrder();

    $mail = (new OrderConfirmation($order, 'A note from the shop'))->toMail(new AnonymousNotifiable);
    $html = (string) $mail->render();

    expect($mail->subject)->toBe('Your order ORD-1 has been received')
        ->and($mail->markdown)->toBe('lunar::mail.orders.confirmation')
        ->and($html)->toContain('Hi Ada,')
        ->and($html)->toContain('ORD-1')
        ->and($html)->toContain('Widget (Blue)')
        ->and($html)->toContain('Gadget')
        ->and($html)->not->toContain('Standard delivery')
        ->and($html)->toContain('£100.00')
        ->and($html)->toContain('-£10.00')
        ->and($html)->toContain('£174.00')
        ->and($html)->toContain('Difference Engines Ltd')
        ->and($html)->toContain('United Kingdom')
        ->and($html)->toContain('Paid')
        ->and($html)->toContain('A note from the shop');
});

test('the confirmation greets anonymously when the order has no contact name', function () {
    $order = Order::factory()->create(['reference' => 'ORD-2', 'placed_at' => now()]);

    expect(renderNotification(new OrderConfirmation($order)))->toContain('Hello,');
});

test('the payment received email renders the order total', function () {
    $order = notificationOrder();

    $mail = (new PaymentReceived($order))->toMail(new AnonymousNotifiable);

    expect($mail->subject)->toBe('Payment received for order ORD-1')
        ->and((string) $mail->render())->toContain('£174.00');
});

test('the shipped email lists the fulfilment lines and a tracking link per reference', function () {
    $order = notificationOrder();
    $lines = $order->lines()->whereType('physical')->get();

    $fulfilment = $order->createFulfilment([$lines[0]->id => 1, $lines[1]->id => 1]);
    $fulfilment->addTracking(['carrier' => 'acme', 'tracking_number' => 'AB123', 'tracking_url' => 'https://track.example/AB123']);
    $fulfilment->addTracking(['carrier' => null, 'tracking_number' => 'CD456', 'tracking_url' => null]);

    $mail = (new OrderShipped($fulfilment->fresh()))->toMail(new AnonymousNotifiable);
    $html = (string) $mail->render();

    expect($mail->subject)->toBe('Your order ORD-1 is on its way')
        ->and($html)->toContain('Widget (Blue)')
        ->and($html)->toContain('Gadget')
        ->and($html)->toContain('href="https://track.example/AB123"')
        ->and($html)->toContain('AB123')
        ->and($html)->toContain('CD456');
});

test('the shipped email omits the tracking block when the fulfilment has no references', function () {
    $order = notificationOrder();
    $line = $order->lines()->whereType('physical')->first();

    $html = renderNotification(new OrderShipped($order->createFulfilment([$line->id => 1])));

    expect($html)->not->toContain(__('lunar::notifications.order_shipped.tracking'))
        ->and($html)->toContain('Widget');
});

test('the ready for collection email names the location', function () {
    $order = notificationOrder();
    $line = $order->lines()->whereType('physical')->first();

    $fulfilment = $order->createFulfilment([$line->id => 2], ['method' => 'collection']);

    $mail = (new OrderReadyForCollection($fulfilment))->toMail(new AnonymousNotifiable);

    expect($mail->subject)->toBe('Your order ORD-1 is ready to collect')
        ->and((string) $mail->render())->toContain('Camden store')
        ->and((string) $mail->render())->toContain('Widget');
});

test('the provisioned and return received emails list the fulfilment lines', function () {
    $order = notificationOrder();
    $line = $order->lines()->whereType('physical')->first();
    $fulfilment = $order->createFulfilment([$line->id => 2]);

    expect(renderNotification(new OrderProvisioned($fulfilment)))->toContain('Widget')
        ->and(renderNotification(new ReturnReceived($fulfilment)))->toContain('Widget');
});

test('the cancellation email renders the cancel reason label', function () {
    $order = notificationOrder();
    $order->cancel(reason: 'items-unavailable', notify: false);

    $mail = (new OrderCancellation($order->fresh()))->toMail(new AnonymousNotifiable);

    expect($mail->subject)->toBe('Your order ORD-1 has been cancelled')
        ->and((string) $mail->render())->toContain($order->fresh()->cancelReasonLabel());
});

test('the refund email reads the latest successful refund from the ledger', function () {
    $order = notificationOrder();

    Transaction::factory()->for($order)->create(['type' => 'capture', 'success' => true, 'amount' => 17400]);
    Transaction::factory()->for($order)->create(['type' => 'refund', 'success' => true, 'amount' => 2500]);
    Transaction::factory()->for($order)->create(['type' => 'refund', 'success' => false, 'amount' => 9900]);
    Transaction::factory()->for($order)->create(['type' => 'refund', 'success' => true, 'amount' => 1200]);

    $mail = (new RefundIssued($order->fresh()))->toMail(new AnonymousNotifiable);

    expect($mail->subject)->toBe('A refund has been issued for order ORD-1')
        ->and((string) $mail->render())->toContain('£12.00')
        ->and((string) $mail->render())->not->toContain('£99.00');
});

test('the refund email omits the amount when no refund has been recorded', function () {
    $order = notificationOrder();

    expect(renderNotification(new RefundIssued($order)))->not->toContain(__('lunar::notifications.partials.refund_amount'));
});

test('the partial fulfilment update lists only the quantities still to be dispatched', function () {
    $order = notificationOrder();
    [$widget, $gadget] = $order->lines()->whereType('physical')->orderBy('id')->get();

    $order->createFulfilment([$widget->id => 1])->ship(notify: false);
    $order->createFulfilment([$gadget->id => 1]);

    $html = renderNotification(new PartialFulfilmentUpdate($order->fresh()));

    expect($html)->toContain('Widget (Blue)')
        ->and($html)->toContain('Gadget')
        ->and($html)->toContain(__('lunar::notifications.partial_fulfilment_update.outstanding'));

    // Widget: 2 ordered, 1 shipped; Gadget: nothing dispatched yet.
    $rows = (new PartialFulfilmentUpdate($order->fresh()))->toMail(new AnonymousNotifiable)->viewData['lines'];

    expect($rows)->toBe([
        ['description' => 'Widget', 'option' => 'Blue', 'quantity' => 1],
        ['description' => 'Gadget', 'option' => null, 'quantity' => 1],
    ]);
});

test('the partial fulfilment update omits the list once everything has been dispatched', function () {
    $order = notificationOrder();
    [$widget, $gadget] = $order->lines()->whereType('physical')->orderBy('id')->get();

    $order->createFulfilment([$widget->id => 2, $gadget->id => 1])->ship(notify: false);

    expect(renderNotification(new PartialFulfilmentUpdate($order->fresh())))
        ->not->toContain(__('lunar::notifications.partial_fulfilment_update.outstanding'));
});

test('order-scoped defaults accept a customer message and fulfilment-scoped ones do not', function () {
    foreach (['order-confirmation', 'payment-received', 'order-cancelled', 'refund-issued', 'partial-fulfilment-update', 'order-update'] as $key) {
        expect(is_subclass_of(OrderNotifications::get($key), AcceptsCustomerMessage::class))->toBeTrue($key);
    }

    foreach (['order-shipped', 'order-ready-for-collection', 'order-provisioned', 'return-received'] as $key) {
        expect(is_subclass_of(OrderNotifications::get($key), AcceptsCustomerMessage::class))->toBeFalse($key);
    }
});

test('the tracking partial falls back to the raw carrier key for an unregistered carrier', function () {
    Carriers::forget('acme');

    $order = notificationOrder();
    $line = $order->lines()->whereType('physical')->first();
    $fulfilment = $order->createFulfilment([$line->id => 1]);
    $fulfilment->addTracking(['carrier' => 'acme', 'tracking_number' => 'ZZ9']);

    expect(renderNotification(new OrderShipped($fulfilment->fresh())))->toContain('acme');
});
