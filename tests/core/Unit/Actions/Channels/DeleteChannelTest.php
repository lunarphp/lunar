<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Channels\DeleteChannel;
use Lunar\Core\Exceptions\ChannelActionException;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Order;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

it('deletes a channel with no order history', function () {
    $channel = Channel::factory()->create();

    app(DeleteChannel::class)->execute($channel);

    expect(Channel::find($channel->id))->toBeNull();
});

it('rejects deleting a channel with order history', function () {
    $channel = Channel::factory()->create();

    Order::factory()->create(['channel_id' => $channel->id]);

    app(DeleteChannel::class)->execute($channel);
})->throws(ChannelActionException::class);

it('does not delete a channel with order history', function () {
    $channel = Channel::factory()->create();

    Order::factory()->create(['channel_id' => $channel->id]);

    try {
        app(DeleteChannel::class)->execute($channel);
    } catch (ChannelActionException) {
        // expected
    }

    expect(Channel::find($channel->id))->not->toBeNull();
});
