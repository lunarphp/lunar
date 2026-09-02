<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Checkout\Contracts\CheckoutDriver;
use Lunar\Core\Facades\CartSession;
use Lunar\Core\Models\Address;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\Customer;
use Lunar\Tests\Checkout\TestCase;
use Lunar\Tests\Checkout\Utils\CheckoutCart;
use Lunar\Tests\Core\Stubs\User;

uses(TestCase::class, RefreshDatabase::class);

/** @return array{0: User, 1: Customer} */
function savedAddressUser(): array
{
    $user = User::factory()->create();
    $customer = Customer::factory()->create();
    $user->customers()->attach($customer);

    return [$user, $customer];
}

it('projects the signed-in customer\'s address book, shipping default first', function () {
    [$user, $customer] = savedAddressUser();
    $country = Country::factory()->create(['iso2' => 'GB']);

    Address::factory()->create([
        'customer_id' => $customer->id,
        'country_id' => $country->id,
        'title' => null,
        'first_name' => 'Alec',
        'last_name' => 'Ritson',
        'line_one' => '12 Trade Counter Way',
        'city' => 'London',
        'postcode' => 'SE1 1AA',
        'contact_phone' => '07000000000',
        'shipping_default' => false,
    ]);
    Address::factory()->create([
        'customer_id' => $customer->id,
        'country_id' => $country->id,
        'title' => 'Dartford branch',
        'first_name' => 'Alec',
        'last_name' => 'Ritson',
        'line_one' => '1 Depot Road',
        'city' => 'Dartford',
        'postcode' => 'DA1 1AA',
        'shipping_default' => true,
    ]);

    $cart = CheckoutCart::addLine(routeTestCart());
    $session = app(CheckoutDriver::class)->createSession($cart);
    CartSession::use($cart);
    $this->actingAs($user);

    $this->get(route('lunar.checkout.show', $session->uuid), ['X-Inertia' => 'true'])
        ->assertOk()
        ->assertJsonCount(2, 'props.checkout.savedAddresses')
        ->assertJsonPath('props.checkout.savedAddresses.0.title', 'Dartford branch')
        ->assertJsonPath('props.checkout.savedAddresses.0.shippingDefault', true)
        ->assertJsonPath('props.checkout.savedAddresses.0.address.line1', '1 Depot Road')
        ->assertJsonPath('props.checkout.savedAddresses.0.address.countryCode', 'GB')
        ->assertJsonPath('props.checkout.savedAddresses.1.address.line1', '12 Trade Counter Way')
        ->assertJsonPath('props.checkout.savedAddresses.1.address.firstName', 'Alec')
        ->assertJsonPath('props.checkout.savedAddresses.1.address.phone', '07000000000');
});

it('leads with the last-used address ahead of the shipping default', function () {
    [$user, $customer] = savedAddressUser();
    $country = Country::factory()->create(['iso2' => 'GB']);

    Address::factory()->create([
        'customer_id' => $customer->id,
        'country_id' => $country->id,
        'line_one' => '1 Depot Road',
        'shipping_default' => true,
        'last_used_at' => null,
    ]);
    Address::factory()->create([
        'customer_id' => $customer->id,
        'country_id' => $country->id,
        'line_one' => '12 Trade Counter Way',
        'shipping_default' => false,
        'last_used_at' => now()->subDay(),
    ]);

    $cart = CheckoutCart::addLine(routeTestCart());
    $session = app(CheckoutDriver::class)->createSession($cart);
    CartSession::use($cart);
    $this->actingAs($user);

    $this->get(route('lunar.checkout.show', $session->uuid), ['X-Inertia' => 'true'])
        ->assertOk()
        ->assertJsonPath('props.checkout.savedAddresses.0.address.line1', '12 Trade Counter Way')
        ->assertJsonPath('props.checkout.savedAddresses.1.address.line1', '1 Depot Road');
});

it('stamps a saved address as last used when it is stored as the delivery address', function () {
    [$user, $customer] = savedAddressUser();
    $country = Country::factory()->create(['iso2' => 'GB']);

    $saved = Address::factory()->create([
        'customer_id' => $customer->id,
        'country_id' => $country->id,
        'line_one' => '1 Depot Road',
        'postcode' => 'DA1 1AA',
        'last_used_at' => null,
    ]);
    $other = Address::factory()->create([
        'customer_id' => $customer->id,
        'country_id' => $country->id,
        'line_one' => '99 Elsewhere Street',
        'postcode' => 'ZZ9 9ZZ',
        'last_used_at' => null,
    ]);

    $cart = CheckoutCart::addLine(routeTestCart());
    $session = app(CheckoutDriver::class)->createSession($cart);
    CartSession::use($cart);
    $this->actingAs($user);

    $this->post(route('lunar.checkout.shipping-address.store', $session->uuid), [
        'first_name' => 'Alec',
        'last_name' => 'Ritson',
        'line1' => '1 Depot Road',
        'city' => 'Dartford',
        'postcode' => 'DA1 1AA',
        'country_code' => 'GB',
    ])->assertRedirect();

    expect($saved->refresh()->last_used_at)->not->toBeNull()
        ->and($other->refresh()->last_used_at)->toBeNull();
});

it('does not stamp saved addresses for a hand-entered address that matches none', function () {
    [$user, $customer] = savedAddressUser();
    $country = Country::factory()->create(['iso2' => 'GB']);

    $saved = Address::factory()->create([
        'customer_id' => $customer->id,
        'country_id' => $country->id,
        'line_one' => '1 Depot Road',
        'postcode' => 'DA1 1AA',
        'last_used_at' => null,
    ]);

    $cart = CheckoutCart::addLine(routeTestCart());
    $session = app(CheckoutDriver::class)->createSession($cart);
    CartSession::use($cart);
    $this->actingAs($user);

    $this->post(route('lunar.checkout.shipping-address.store', $session->uuid), [
        'first_name' => 'Alec',
        'last_name' => 'Ritson',
        'line1' => '55 Somewhere New',
        'city' => 'Maidstone',
        'postcode' => 'ME1 1AA',
        'country_code' => 'GB',
    ])->assertRedirect();

    expect($saved->refresh()->last_used_at)->toBeNull();
});

it('projects an empty address book for guests', function () {
    $cart = CheckoutCart::addLine(routeTestCart());
    $session = app(CheckoutDriver::class)->createSession($cart);
    CartSession::use($cart);

    $this->get(route('lunar.checkout.show', $session->uuid), ['X-Inertia' => 'true'])
        ->assertOk()
        ->assertJsonPath('props.checkout.savedAddresses', []);
});

it('never projects another customer\'s address book', function () {
    // Some OTHER customer owns addresses; the signed-in user has none.
    $stranger = Customer::factory()->create();
    Address::factory()->create(['customer_id' => $stranger->id]);

    [$user] = savedAddressUser();

    $cart = CheckoutCart::addLine(routeTestCart());
    $session = app(CheckoutDriver::class)->createSession($cart);
    CartSession::use($cart);
    $this->actingAs($user);

    $this->get(route('lunar.checkout.show', $session->uuid), ['X-Inertia' => 'true'])
        ->assertOk()
        ->assertJsonPath('props.checkout.savedAddresses', []);
});
