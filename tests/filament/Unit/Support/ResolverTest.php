<?php

use Lunar\Filament\Schemas\Brand\BrandForm;
use Lunar\Filament\Support\Resolver;
use Lunar\Tests\Filament\TestCase;

uses(TestCase::class);

it('returns the bridge class when no published subclass exists', function () {
    expect(Resolver::resolve(BrandForm::class))->toBe(BrandForm::class);
});

it('returns the bridge class when prefer_published is disabled', function () {
    config()->set('lunar.filament.resolver.prefer_published', false);

    expect(Resolver::resolve(BrandForm::class))->toBe(BrandForm::class);
});

it('passes through non-bridge classes untouched', function () {
    expect(Resolver::resolve('App\\Something\\NotABridgeClass'))
        ->toBe('App\\Something\\NotABridgeClass');
});
