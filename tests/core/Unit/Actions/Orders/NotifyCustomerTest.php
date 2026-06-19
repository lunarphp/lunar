<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Lunar\Core\Contracts\Notifications\AcceptsCustomerMessage;
use Lunar\Core\Events\Orders\OrderCustomerNotified;
use Lunar\Core\Exceptions\OrderActionException;
use Lunar\Core\Facades\CustomerNotifications;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderAddress;
use Lunar\Tests\Core\TestCase;
use Spatie\Activitylog\Models\Activity;

uses(TestCase::class);
uses(RefreshDatabase::class);

class FakeOrderUpdateNotification extends Notification implements AcceptsCustomerMessage
{
    public function __construct(public Order $order, public ?string $message = null) {}

    public function via(): array
    {
        return ['mail'];
    }
}

beforeEach(function () {
    activity()->enableLogging();
    Language::factory()->create(['default' => true, 'code' => 'en']);
    Currency::factory()->create(['default' => true]);
    Location::factory()->default()->create();

    CustomerNotifications::register('order-update', FakeOrderUpdateNotification::class, 'Order update');

    $this->order = Order::factory()->create();
    $this->order->addresses()->create(OrderAddress::factory()->raw([
        'type' => 'billing',
        'contact_email' => 'billing@example.com',
    ]));
    $this->order->addresses()->create(OrderAddress::factory()->raw([
        'type' => 'shipping',
        'contact_email' => 'shipping@example.com',
    ]));
});

afterEach(function () {
    CustomerNotifications::forget('order-update');
});

test('sends the chosen notification to the order contacts and logs each send', function () {
    NotificationFacade::fake();
    Event::fake([OrderCustomerNotified::class]);

    $this->order->notifyCustomer('order-update');

    NotificationFacade::assertSentOnDemand(
        FakeOrderUpdateNotification::class,
        fn (Notification $notification, array $channels, AnonymousNotifiable $notifiable) => $notifiable->routes['mail'] === 'billing@example.com'
    );
    NotificationFacade::assertSentOnDemand(
        FakeOrderUpdateNotification::class,
        fn (Notification $notification, array $channels, AnonymousNotifiable $notifiable) => $notifiable->routes['mail'] === 'shipping@example.com'
    );

    $logs = Activity::query()->where('event', 'email-notification')->get();
    expect($logs)->toHaveCount(2)
        ->and($logs->pluck('properties.email')->sort()->values()->all())
        ->toBe(['billing@example.com', 'shipping@example.com'])
        ->and($logs->first()->getExtraProperty('notification'))->toBe('Order update');

    Event::assertDispatched(
        OrderCustomerNotified::class,
        fn (OrderCustomerNotified $e) => $e->notification === 'order-update'
            && $e->recipients === ['billing@example.com', 'shipping@example.com']
    );
});

test('sends to explicit recipients when provided', function () {
    NotificationFacade::fake();

    $this->order->notifyCustomer('order-update', recipients: ['ad-hoc@example.com']);

    NotificationFacade::assertSentOnDemand(
        FakeOrderUpdateNotification::class,
        fn (Notification $notification, array $channels, AnonymousNotifiable $notifiable) => $notifiable->routes['mail'] === 'ad-hoc@example.com'
    );

    expect(Activity::query()->where('event', 'email-notification')->count())->toBe(1);
});

test('dedupes recipients so a shared contact email is only emailed once', function () {
    NotificationFacade::fake();

    $this->order->notifyCustomer('order-update', recipients: ['dupe@example.com', 'dupe@example.com']);

    expect(Activity::query()->where('event', 'email-notification')->count())->toBe(1);
});

test('passes the custom message through to the notification and the log', function () {
    NotificationFacade::fake();

    $this->order->notifyCustomer('order-update', 'Sorry for the delay', ['one@example.com']);

    NotificationFacade::assertSentOnDemand(
        FakeOrderUpdateNotification::class,
        fn (FakeOrderUpdateNotification $notification) => $notification->message === 'Sorry for the delay'
    );

    expect(Activity::query()->where('event', 'email-notification')->first()->getExtraProperty('message'))
        ->toBe('Sorry for the delay');
});

test('throws when the notification key is not registered', function () {
    NotificationFacade::fake();

    $this->order->notifyCustomer('not-a-real-key');
})->throws(OrderActionException::class);

test('throws when no recipient email is available', function () {
    NotificationFacade::fake();

    $order = Order::factory()->create();

    $order->notifyCustomer('order-update');
})->throws(OrderActionException::class);
