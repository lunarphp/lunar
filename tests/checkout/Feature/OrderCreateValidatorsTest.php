<?php

use Lunar\Checkout\Validation\Cart\PickupPointRequired;
use Lunar\Core\Validation\Cart\ValidateCartForOrderCreation;
use Lunar\Tests\Checkout\Utils\BareCartValidatorsTestCase;

uses(BareCartValidatorsTestCase::class);

it('keeps core\'s validator when the host config carries no order_create key', function () {
    $validators = config('lunar.cart.validators.order_create');

    expect($validators)->toContain(ValidateCartForOrderCreation::class)
        ->and($validators)->toContain(PickupPointRequired::class);
});
