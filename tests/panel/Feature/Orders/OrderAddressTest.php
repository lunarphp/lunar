<?php

use Lunar\Core\Models\Country;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;
use Spatie\Activitylog\Models\Activity;

uses(TestCase::class);

beforeEach(function () {
    Currency::factory()->create(['code' => 'GBP', 'default' => true, 'exchange_rate' => 1]);
    Language::factory()->create(['default' => true, 'code' => 'en']);
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');
});

/** @return array<string, mixed> */
function validOrderAddressPayload(Country $country, array $overrides = []): array
{
    return array_merge([
        'first_name' => 'Ada',
        'last_name' => 'Lovelace',
        'line_one' => '12 Analytical Row',
        'city' => 'London',
        'postcode' => 'E1 6AN',
        'country_id' => $country->id,
    ], $overrides);
}

it('updates an order address and logs the change on the order timeline', function () {
    // The suite disables activity logging globally; this test asserts on it.
    activity()->enableLogging();

    $country = Country::factory()->create();
    $order = Order::factory()->placed()->create();
    $address = $order->addresses()->create([
        'type' => 'shipping',
        'first_name' => 'Ada',
        'last_name' => 'Lovelace',
        'line_one' => '1 Wrong Street',
        'city' => 'London',
        'postcode' => 'E1 6AN',
        'country_id' => $country->id,
    ]);

    $this->from(route('panel.orders.show', $order))
        ->put(route('panel.orders.addresses.update', [$order, $address]), validOrderAddressPayload($country, [
            'line_one' => '12 Analytical Row',
        ]))
        ->assertSessionHas('success');

    expect($address->refresh()->line_one)->toBe('12 Analytical Row');

    $activity = Activity::query()
        ->where('event', 'order-address-update')
        ->where('subject_type', $order->getMorphClass())
        ->where('subject_id', $order->id)
        ->latest('id')
        ->first();

    expect($activity)->not->toBeNull();
    expect($activity->getExtraProperty('type'))->toBe('shipping');
    expect($activity->getExtraProperty('previous')['line_one'])->toBe('1 Wrong Street');
    expect($activity->getExtraProperty('new')['line_one'])->toBe('12 Analytical Row');
});

it('logs nothing when the address is saved unchanged', function () {
    activity()->enableLogging();

    $country = Country::factory()->create();
    $order = Order::factory()->placed()->create();
    $address = $order->addresses()->create([
        'type' => 'billing',
        ...validOrderAddressPayload($country),
    ]);

    $this->from(route('panel.orders.show', $order))
        ->put(route('panel.orders.addresses.update', [$order, $address]), validOrderAddressPayload($country))
        ->assertSessionHas('success');

    expect(Activity::query()->where('event', 'order-address-update')->count())->toBe(0);
});

it('validates the address fields', function () {
    $country = Country::factory()->create();
    $order = Order::factory()->placed()->create();
    $address = $order->addresses()->create([
        'type' => 'shipping',
        ...validOrderAddressPayload($country),
    ]);

    $this->from(route('panel.orders.show', $order))
        ->put(route('panel.orders.addresses.update', [$order, $address]), [
            'first_name' => '',
            'line_one' => '',
            'city' => '',
            'country_id' => 999999,
        ])
        ->assertSessionHasErrors(['first_name', 'line_one', 'city', 'country_id']);
});

it('scopes the address route to the order', function () {
    $country = Country::factory()->create();
    $order = Order::factory()->placed()->create();
    $otherOrder = Order::factory()->placed()->create();
    $foreign = $otherOrder->addresses()->create([
        'type' => 'shipping',
        ...validOrderAddressPayload($country),
    ]);

    $this->put(route('panel.orders.addresses.update', [$order, $foreign]), validOrderAddressPayload($country))
        ->assertNotFound();
});
