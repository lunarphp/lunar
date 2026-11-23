<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Lunar\Core\Actions\Channels\DeleteChannel;
use Lunar\Core\Exceptions\ChannelActionException;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\Region;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class)->group('cross-db');
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

it('deletes a channel that products and collections are attached to, removing its availability rows', function () {
    Channel::factory()->create(['default' => true]);
    $channel = Channel::factory()->create(['default' => false]);

    // Creating a product or collection attaches every channel, enabled or not.
    $product = Product::factory()->create();
    $collection = Collection::factory()->create();
    $product->channels()->updateExistingPivot($channel->id, ['enabled' => true]);

    expect($product->channels()->whereKey($channel->id)->exists())->toBeTrue()
        ->and($collection->channels()->whereKey($channel->id)->exists())->toBeTrue();

    app(DeleteChannel::class)->execute($channel);

    expect(Channel::find($channel->id))->toBeNull()
        ->and(DB::table(config('lunar.database.table_prefix').'channelables')->where('channel_id', $channel->id)->exists())->toBeFalse()
        ->and(Product::find($product->id))->not->toBeNull()
        ->and(Collection::find($collection->id))->not->toBeNull()
        ->and($product->channels()->count())->toBe(1);
});

it('does not delete a channel that a region uses', function () {
    $channel = Channel::factory()->create(['default' => false]);
    Region::factory()->create(['channel_id' => $channel->id]);
    Product::factory()->create();

    expect(fn () => app(DeleteChannel::class)->execute($channel))
        ->toThrow(ChannelActionException::class, 'Cannot delete a channel that a region uses.');

    expect(Channel::find($channel->id))->not->toBeNull()
        ->and(DB::table(config('lunar.database.table_prefix').'channelables')->where('channel_id', $channel->id)->exists())->toBeTrue();
});

it('does not delete a channel that carts use', function () {
    $channel = Channel::factory()->create(['default' => false]);
    Cart::factory()->create(['channel_id' => $channel->id]);

    expect(fn () => app(DeleteChannel::class)->execute($channel))
        ->toThrow(ChannelActionException::class, 'Cannot delete a channel that carts use.');

    expect(Channel::find($channel->id))->not->toBeNull();
});

it('does not delete a channel with order history or its availability rows', function () {
    $channel = Channel::factory()->create(['default' => false]);
    Product::factory()->create();
    Order::factory()->create(['channel_id' => $channel->id]);

    expect(fn () => app(DeleteChannel::class)->execute($channel))
        ->toThrow(ChannelActionException::class, 'Cannot delete a channel with order history.');

    expect(DB::table(config('lunar.database.table_prefix').'channelables')->where('channel_id', $channel->id)->exists())->toBeTrue();
});
