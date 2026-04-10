<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Lunar\Models\Channel;
use Lunar\Models\Discount;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class)->group('models');

uses(RefreshDatabase::class);

test('can make a channel', function () {
    $channel = Channel::factory()->create([
        'name' => 'Webstore',
        'handle' => 'webstore',
        'default' => true,
        'url' => 'http://mystore.test',
    ]);

    expect($channel->name)->toEqual('Webstore');
    expect($channel->handle)->toEqual('webstore');
    expect($channel->default)->toBeTrue();
    expect($channel->url)->toEqual('http://mystore.test');
});

test('changes are recorded in activity log', function () {
    activity()->enableLogging();

    $channel = Channel::factory()->create([
        'name' => 'Webstore',
    ]);

    $channel->update([
        'name' => 'Foobar',
    ]);

    $log = $channel->activities()->whereEvent('updated')->first();

    expect($log)->not->toBeNull();
});

test('can return associated discounts', function () {

    $channel = Channel::factory()->create();

    // Stop observers creating the channel association.
    Event::fake();

    $discount = Discount::factory()->create();

    expect($channel->discounts)->toHaveCount(0);

    $discount->channels()->attach($channel->id);

    expect($channel->refresh()->discounts)->toHaveCount(1);
});

test('can soft delete a channel', function () {
    $channel = Channel::factory()->create();

    $channel->delete();

    \Pest\Laravel\assertDatabaseHas(Channel::class, [
        'id' => $channel->id,
        'deleted_at' => now(),
    ]);
});
