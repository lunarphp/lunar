<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Channels\UpdateChannel;
use Lunar\Core\Exceptions\ChannelActionException;
use Lunar\Core\Models\Channel;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

it('updates a channel', function () {
    $channel = Channel::factory()->create(['default' => false]);

    $updated = app(UpdateChannel::class)->execute($channel, [
        'name' => 'Wholesale',
        'url' => 'https://wholesale.example.com',
    ]);

    expect($updated->id)->toBe($channel->id);
    expect($updated->fresh()->name)->toBe('Wholesale');
    expect($updated->fresh()->handle)->toBe('wholesale');
    expect($updated->fresh()->url)->toBe('https://wholesale.example.com');
});

it('unsets default on every other channel when this channel becomes default', function () {
    $existingDefault = Channel::factory()->create(['default' => true]);
    $channel = Channel::factory()->create(['default' => false]);

    app(UpdateChannel::class)->execute($channel, [
        'name' => $channel->name,
        'default' => true,
    ]);

    expect($existingDefault->fresh()->default)->toBeFalse();
    expect($channel->fresh()->default)->toBeTrue();
    expect(Channel::where('default', true)->count())->toBe(1);
});

it('does not unset other defaults when this channel is not becoming default', function () {
    $existingDefault = Channel::factory()->create(['default' => true]);
    $channel = Channel::factory()->create(['default' => false]);

    app(UpdateChannel::class)->execute($channel, [
        'name' => $channel->name,
    ]);

    expect($existingDefault->fresh()->default)->toBeTrue();
    expect($channel->fresh()->default)->toBeFalse();
});

it('does not unset itself when it is already the default channel', function () {
    $channel = Channel::factory()->create(['default' => true]);

    app(UpdateChannel::class)->execute($channel, [
        'name' => $channel->name,
        'default' => true,
    ]);

    expect($channel->fresh()->default)->toBeTrue();
});

it('rejects unsetting default on the default channel', function () {
    $channel = Channel::factory()->create(['default' => true]);

    app(UpdateChannel::class)->execute($channel, [
        'name' => $channel->name,
        'default' => false,
    ]);
})->throws(ChannelActionException::class);

it('keeps the default channel unchanged when unsetting default is rejected', function () {
    $channel = Channel::factory()->create(['name' => 'Webstore', 'default' => true]);

    try {
        app(UpdateChannel::class)->execute($channel, [
            'name' => 'Renamed',
            'default' => false,
        ]);
    } catch (ChannelActionException) {
        // expected
    }

    expect($channel->fresh()->default)->toBeTrue();
    expect($channel->fresh()->name)->toBe('Webstore');
});

it('updates the default channel when default stays untouched', function () {
    $channel = Channel::factory()->create(['default' => true]);

    app(UpdateChannel::class)->execute($channel, [
        'name' => 'Renamed',
    ]);

    expect($channel->fresh()->name)->toBe('Renamed');
    expect($channel->fresh()->default)->toBeTrue();
});
