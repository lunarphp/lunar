<?php

use Lunar\Stripe\Concerns\ProcessesEventParameters;
use Lunar\Stripe\DataTransferObjects\EventParameters;
use Lunar\Tests\Stripe\Unit\TestCase;
use Stripe\Event;
use Stripe\StripeObject;

uses(TestCase::class);

it('can process event parameters', function () {
    $event = new Event;
    $event->data = new StripeObject;
    $event->data->object = new StripeObject('PAYMENT_INTENT_ID');
    $event->data->object->metadata = new StripeObject;
    $event->data->object->metadata->order_id = 25;

    $dto = app(ProcessesEventParameters::class)->handle($event);

    expect($dto)->toBeInstanceOf(EventParameters::class)
        ->and($dto->paymentIntentId)->toBe('PAYMENT_INTENT_ID')
        ->and($dto->orderId)->toBe(25);
})->group('lunar.stripe.actions');

it('can replace event parameters action', function () {
    $event = new Event;
    $event->data = new StripeObject;
    $event->data->object = new StripeObject('PAYMENT_INTENT_ID');
    $event->data->object->metadata = new StripeObject;
    $event->data->object->metadata->order_id = 25;

    \Pest\Laravel\instance(ProcessesEventParameters::class, new class implements ProcessesEventParameters
    {
        public function handle(Event $event): EventParameters
        {
            return new EventParameters('INTENT_TWO', 566);
        }
    });

    $dto = app(ProcessesEventParameters::class)->handle($event);

    expect($dto)->toBeInstanceOf(EventParameters::class)
        ->and($dto->paymentIntentId)->toBe('INTENT_TWO')
        ->and($dto->orderId)->toBe(566);
})->group('lunar.stripe.actions');
