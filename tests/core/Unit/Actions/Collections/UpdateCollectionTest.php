<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Collections\UpdateCollection;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Language;
use Lunar\Core\States\Collection\Published;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
    Currency::factory()->create(['default' => true, 'decimal_places' => 2]);
});

test('updates the given attributes', function () {
    $collection = Collection::factory()->create();

    app(UpdateCollection::class)->execute($collection, [
        'name' => ['en' => 'Winter Warmers'],
        'handle' => 'winter-warmers',
        'status' => 'published',
        'sort' => 'min_price:asc',
        'short_description' => ['en' => 'Cold-weather picks.'],
    ]);

    $collection->refresh();

    expect($collection->translate('name'))->toBe('Winter Warmers');
    expect($collection->handle)->toBe('winter-warmers');
    expect($collection->status)->toBeInstanceOf(Published::class);
    expect($collection->sort)->toBe('min_price:asc');
    expect($collection->translate('short_description'))->toBe('Cold-weather picks.');
});

test('syncs channel availability when given', function () {
    $channel = Channel::factory()->create();
    $collection = Collection::factory()->create();

    app(UpdateCollection::class)->execute($collection, [], channels: [
        $channel->id => [
            'enabled' => true,
            'starts_at' => now()->addDay(),
            'ends_at' => null,
        ],
    ]);

    $pivot = $collection->channels()->first()?->pivot;

    expect((bool) $pivot->enabled)->toBeTrue();
    expect($pivot->starts_at)->not->toBeNull();
});

test('syncs customer group availability when given', function () {
    $group = CustomerGroup::factory()->create();
    $collection = Collection::factory()->create();

    app(UpdateCollection::class)->execute($collection, [], customerGroups: [
        $group->id => [
            'enabled' => true,
            'visible' => false,
            'starts_at' => null,
            'ends_at' => null,
        ],
    ]);

    $pivot = $collection->customerGroups()->first()?->pivot;

    expect((bool) $pivot->enabled)->toBeTrue();
    expect((bool) $pivot->visible)->toBeFalse();
});

test('null leaves availability untouched', function () {
    $channel = Channel::factory()->create();
    $collection = Collection::factory()->create();

    $collection->channels()->sync([
        $channel->id => ['enabled' => true, 'starts_at' => null, 'ends_at' => null],
    ]);

    app(UpdateCollection::class)->execute($collection, [
        'name' => ['en' => 'Renamed'],
    ]);

    expect((bool) $collection->channels()->first()->pivot->enabled)->toBeTrue();
});
