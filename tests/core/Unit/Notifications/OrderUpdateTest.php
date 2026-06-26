<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\Order;
use Lunar\Core\Notifications\OrderUpdate;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
    Currency::factory()->create(['default' => true]);
    Location::factory()->default()->create();
});

test('renders the order reference and the custom message into the mail', function () {
    $order = Order::factory()->create(['reference' => 'ABC-123']);

    $mail = (new OrderUpdate($order, 'Sorry for the delay'))->toMail(new AnonymousNotifiable);

    expect($mail->subject)->toContain('ABC-123')
        ->and($mail->introLines)->toContain('Sorry for the delay')
        ->and($mail->introLines)->toHaveCount(3);
});

test('omits the message line when no message is given', function () {
    $order = Order::factory()->create(['reference' => 'ABC-123']);

    $mail = (new OrderUpdate($order))->toMail(new AnonymousNotifiable);

    expect($mail->introLines)->toHaveCount(2);
});
