<?php

uses(\Lunar\Tests\Core\TestCase::class);

use Illuminate\Support\Carbon;
use Lunar\Models\Cart;
use Lunar\Models\Channel;
use Lunar\Models\Currency;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can prune carts with default settings', function () {
    $currency = Currency::factory()->create();
    $channel = Channel::factory()->create();

    $cart = Cart::create([
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
        'meta' => ['foo' => 'bar'],
        'updated_at' => Carbon::now()->subDay(120),
    ]);

    $cart = Cart::create([
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
        'meta' => ['foo' => 'bar'],
        'updated_at' => Carbon::now()->subDay(20),
    ]);

    expect(Cart::query()->get())->toHaveCount(2);

    $this->artisan('lunar:prune:carts');

    expect(Cart::query()->get())->toHaveCount(1);
});