<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Lunar\Checkout\Contracts\CheckoutDriver;
use Lunar\Checkout\Contracts\DeliveryCountries;
use Lunar\Checkout\DeliveryCountries\ConfiguredCountries;
use Lunar\Core\Facades\CartSession;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Country;
use Lunar\Tests\Checkout\TestCase;
use Lunar\Tests\Checkout\Utils\CheckoutCart;

uses(TestCase::class, RefreshDatabase::class);

/**
 * Spec 0011 §H. The delivery step offers exactly the countries the bound
 * DeliveryCountries source returns, and the shipping-address store refuses
 * anything outside that list, so nobody saves an address the store cannot
 * ship to and then finds no options.
 */
function deliveryAddress(string $countryCode): array
{
    return [
        'first_name' => 'Ada',
        'last_name' => 'Lovelace',
        'line1' => '12 Analytical Row',
        'city' => 'London',
        'postcode' => 'SW1A 2AA',
        'country_code' => $countryCode,
    ];
}

it('projects the bound delivery countries as code and name pairs', function () {
    $cart = CheckoutCart::orderable();
    $session = CheckoutCart::session($cart);
    $ireland = Country::factory()->create(['iso2' => 'IE', 'name' => 'Ireland']);

    app()->bind(DeliveryCountries::class, fn () => new class($ireland) implements DeliveryCountries
    {
        public function __construct(private Country $only) {}

        public function available(Cart $cart): Collection
        {
            return collect([$this->only]);
        }
    });

    $this->get(route('lunar.checkout.show', $session->uuid), ['X-Inertia' => 'true'])
        ->assertOk()
        ->assertJsonCount(1, 'props.checkout.countries')
        ->assertJsonPath('props.checkout.countries.0', ['code' => 'IE', 'name' => 'Ireland']);
});

it('projects the stored billing address alongside the delivery address', function () {
    $cart = CheckoutCart::orderable();
    $session = CheckoutCart::session($cart);

    $this->get(route('lunar.checkout.show', $session->uuid), ['X-Inertia' => 'true'])
        ->assertOk()
        ->assertJsonPath('props.checkout.billingAddress.postcode', 'SE1 1AA');
});

it('refuses a delivery address outside the offered countries', function () {
    $cart = CheckoutCart::orderable();
    CartSession::use($cart);
    $session = app(CheckoutDriver::class)->resolveOrCreateSession($cart);
    Country::factory()->create(['iso2' => 'IE', 'name' => 'Ireland']);

    config()->set('lunar.checkout.delivery_countries', ['GB']);

    $this->postJson(route('lunar.checkout.shipping-address.store', $session->uuid), deliveryAddress('IE'))
        ->assertUnprocessable()
        ->assertJsonPath('errors.country_code.0', 'We do not deliver to that country.');

    $this->postJson(route('lunar.checkout.shipping-address.store', $session->uuid), deliveryAddress('GB'))
        ->assertRedirect();
});

it('still lets a billing address sit outside the delivery countries', function () {
    $cart = CheckoutCart::orderable();
    CartSession::use($cart);
    $session = app(CheckoutDriver::class)->resolveOrCreateSession($cart);
    Country::factory()->create(['iso2' => 'IE', 'name' => 'Ireland']);

    config()->set('lunar.checkout.delivery_countries', ['GB']);

    $this->postJson(route('lunar.checkout.billing-address.store', $session->uuid), deliveryAddress('IE'))
        ->assertOk();
});

it('offers every country by default and the configured ones in configured order', function () {
    $cart = CheckoutCart::orderable();
    Country::factory()->create(['iso2' => 'IE', 'name' => 'Ireland']);
    Country::factory()->create(['iso2' => 'FR', 'name' => 'France']);

    $source = new ConfiguredCountries;

    expect($source->available($cart)->pluck('iso2')->all())->toContain('GB', 'IE', 'FR');

    config()->set('lunar.checkout.delivery_countries', ['ie', 'GB']);

    expect($source->available($cart)->pluck('iso2')->all())->toBe(['IE', 'GB']);
});
