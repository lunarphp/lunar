<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Channels\DeleteChannel;
use Lunar\Core\Exceptions\ChannelActionException;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Order;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

it('deletes a non-default channel with no order history', function () {
    $channel = Channel::factory()->create(['default' => false]);

    app(DeleteChannel::class)->execute($channel);

    expect(Channel::find($channel->id))->toBeNull();
});

it('rejects deleting a channel with order history', function () {
    $channel = Channel::factory()->create(['default' => false]);

    Order::factory()->create(['channel_id' => $channel->id]);

    app(DeleteChannel::class)->execute($channel);
})->throws(ChannelActionException::class);

it('does not delete a channel with order history', function () {
    $channel = Channel::factory()->create(['default' => false]);

    Order::factory()->create(['channel_id' => $channel->id]);

    try {
        app(DeleteChannel::class)->execute($channel);
    } catch (ChannelActionException) {
        // expected
    }

    expect(Channel::find($channel->id))->not->toBeNull();
});

it('rejects deleting the default channel', function () {
    $channel = Channel::factory()->create(['default' => true]);

    app(DeleteChannel::class)->execute($channel);
})->throws(ChannelActionException::class);

it('does not delete the default channel', function () {
    $channel = Channel::factory()->create(['default' => true]);

    try {
        app(DeleteChannel::class)->execute($channel);
    } catch (ChannelActionException) {
        // expected
    }

    expect(Channel::find($channel->id))->not->toBeNull();
});
