<?php

uses(\Lunar\Tests\TestCase::class);
use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Base\PaymentManagerInterface;
use Lunar\Facades\Payments;
use Lunar\Tests\Stubs\TestPaymentDriver;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('accessor is correct', function () {
    expect(Payments::getFacadeAccessor())->toEqual(PaymentManagerInterface::class);
});

test('can extend payments', function () {
    Payments::extend('testing', function ($app) {
        return $app->make(TestPaymentDriver::class);
    });

    expect(Payments::driver('testing'))->toBeInstanceOf(TestPaymentDriver::class);

    $result = Payments::driver('testing')->authorize();

    expect($result)->toBeInstanceOf(PaymentAuthorize::class);
});
