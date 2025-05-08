<?php

use Lunar\Stripe\Concerns\ProcessesEventParameters;

uses(\Lunar\Tests\Stripe\Unit\TestCase::class);

it('can process event parameters', function () {
    $event = new Stripe\Event;
    $event->data = new Stripe\StripeObject;
    $event->data->object = new Stripe\StripeObject('PAYMENT_INTENT_ID');
    $event->data->object->metadata = new Stripe\StripeObject;
    $event->data->object->metadata->order_id = 25;

    $dto = app(\Lunar\Stripe\Concerns\ProcessesEventParameters::class)->handle($event);

    expect($dto)->toBeInstanceOf(\Lunar\Stripe\DataTransferObjects\EventParameters::class)
        ->and($dto->paymentIntentId)->toBe('PAYMENT_INTENT_ID')
        ->and($dto->orderId)->toBe(25);
})->group('lunar.stripe.actions');

it('can replace event parameters action', function () {
    $event = new Stripe\Event;
    $event->data = new Stripe\StripeObject;
    $event->data->object = new Stripe\StripeObject('PAYMENT_INTENT_ID');
    $event->data->object->metadata = new Stripe\StripeObject;
    $event->data->object->metadata->order_id = 25;

    \Pest\Laravel\instance(ProcessesEventParameters::class, new class implements ProcessesEventParameters
    {
        public function handle(\Stripe\Event $event): \Lunar\Stripe\DataTransferObjects\EventParameters
        {
            return new Lunar\Stripe\DataTransferObjects\EventParameters('INTENT_TWO', 566);
        }
    });

    $dto = app(\Lunar\Stripe\Concerns\ProcessesEventParameters::class)->handle($event);

    expect($dto)->toBeInstanceOf(\Lunar\Stripe\DataTransferObjects\EventParameters::class)
        ->and($dto->paymentIntentId)->toBe('INTENT_TWO')
        ->and($dto->orderId)->toBe(566);
})->group('lunar.stripe.actions');
