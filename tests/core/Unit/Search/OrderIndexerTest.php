<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\Country;
use Lunar\Models\Currency;
use Lunar\Models\Order;
use Lunar\Models\OrderAddress;
use Lunar\Models\OrderLine;
use Lunar\Models\Tag;
use Lunar\Models\Transaction;
use Lunar\Search\OrderIndexer;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class)->group('search', 'indexer');

uses(RefreshDatabase::class);

test('can return correct searchable data', function () {
    Currency::factory()->create([
        'code' => 'GBP',
        'default' => true,
    ]);

    $country = Country::factory()->create([
        'name' => 'United Kingdom',
    ]);

    $order = Order::factory()->create([
        'user_id' => null,
        'placed_at' => now(),
        'meta' => [
            'foo' => 'bar',
        ],
    ]);

    $transaction = Transaction::factory()->create([
        'order_id' => $order->id,
    ]);

    $line = OrderLine::factory()->create([
        'order_id' => $order->id,
        'type' => 'physical',
    ]);

    OrderLine::factory()->create([
        'order_id' => $order->id,
        'type' => 'shipping',
    ]);

    $address = OrderAddress::factory()->create([
        'order_id' => $order->id,
        'country_id' => $country->id,
        'type' => 'shipping',
    ]);

    $tag = Tag::factory()->create([
        'value' => 'vip',
    ]);

    $order->tags()->attach($tag);

    $data = app(OrderIndexer::class)->toSearchableArray($order);

    expect($data['currency_code'])->toEqual('GBP');
    expect($data['channel'])->toEqual($order->channel->name);
    expect($data['total'])->toEqual($order->total->value);
    expect($data['charges']->pluck('reference')->all())->toBe([$transaction->reference]);
    expect($data['lines'])->toEqual([[
        'description' => $line->description,
        'identifier' => $line->identifier,
    ]]);
    expect($data['shipping_first_name'])->toEqual($address->first_name);
    expect($data['shipping_last_name'])->toEqual($address->last_name);
    expect($data['shipping_country'])->toEqual($country->name);
    expect($data['shipping_fullname'])->toEqual($address->first_name.' '.$address->last_name);
    expect($data['tags'])->toBe(['VIP']);
});
