<?php

use Illuminate\Database\Eloquent\Builder;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Product;
use Lunar\Filament\Forms\Components\Support\RecordSearch;
use Lunar\Tests\Filament\TestCase;

uses(TestCase::class);

it('returns a database builder when scout is disabled', function () {
    config()->set('lunar.filament.scout_enabled', false);

    expect(RecordSearch::for(Brand::class, 'acme'))->toBeInstanceOf(Builder::class);
});

it('reports scout usage based on config and trait', function () {
    config()->set('lunar.filament.scout_enabled', true);

    expect(RecordSearch::shouldUseScout(Product::class))->toBeTrue()
        ->and(RecordSearch::shouldUseScout(Attribute::class))->toBeFalse();
});

it('does not use scout when disabled', function () {
    config()->set('lunar.filament.scout_enabled', false);

    expect(RecordSearch::shouldUseScout(Product::class))->toBeFalse();
});
