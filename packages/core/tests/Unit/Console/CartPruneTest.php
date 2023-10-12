<?php

namespace Lunar\Tests\Unit\Console;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Lunar\Models\Cart;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Tests\TestCase;

/**
 * @group commands
 */
class CartPruneTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_prune_carts_with_default_settings()
    {
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
        
        $this->assertCount(2, Cart::query()->get());
        
        $this->artisan('lunar:prune:carts');

        $this->assertCount(1, Cart::query()->get());
    }
}
