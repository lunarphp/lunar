<?php

uses(\Lunar\Tests\TestCase::class);
use Lunar\Models\Channel;
use Lunar\Models\Product;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can scope results to a channel', function () {
    $channelA = Channel::factory()->create([
        'handle' => 'channel-a',
    ]);

    $channelB = Channel::factory()->create([
        'handle' => 'channel-b',
    ]);

    $productA = Product::factory()->create();
    $productB = Product::factory()->create();

    $productA->channels()->syncWithPivotValues([$channelA->id], [
        'starts_at' => now(),
        'enabled' => true,
        'ends_at' => now()->addDay(),
    ]);

    $productB->channels()->syncWithPivotValues([$channelB->id], [
        'starts_at' => now(),
        'enabled' => true,
        'ends_at' => now()->addDay(),
    ]);

    $this->assertDatabaseHas($productA->channels()->getTable(), [
        'channel_id' => $channelA->id,
        'channelable_type' => Product::class,
        'channelable_id' => $productA->id,
        'starts_at' => now(),
        'ends_at' => now()->addDay(),
    ]);

    $resultA = Product::channel($channelA)->get();
    $resultB = Product::channel($channelB)->get();
    $resultC = Product::channel([$channelA, $channelB])->get();
    $resultD = Product::channel()->get();
    $resultE = Product::channel([])->get();
    $resultF = Product::channel(collect())->get();

    expect($resultA)->toHaveCount(1);
    expect($resultB)->toHaveCount(1);
    expect($resultC)->toHaveCount(2);
    expect($resultD)->toHaveCount(2);
    expect($resultE)->toHaveCount(2);
    expect($resultF)->toHaveCount(2);

    expect($resultA->first()->id)->toEqual($productA->id);
    expect($resultB->first()->id)->toEqual($productB->id);

    $productA->channels()->syncWithPivotValues([$channelA->id], [
        'starts_at' => now(),
        'enabled' => false,
        'ends_at' => now()->addDay(),
    ]);

    expect(Product::channel($channelA)->get())->toHaveCount(0);

    $productA->channels()->syncWithPivotValues([$channelA->id], [
        'starts_at' => null,
        'enabled' => true,
        'ends_at' => now()->addDay(),
    ]);

    expect(Product::channel($channelA)->get())->toHaveCount(1);

    $productA->channels()->syncWithPivotValues([$channelA->id], [
        'starts_at' => now()->subDay(),
        'enabled' => true,
        'ends_at' => now()->subHour(),
    ]);

    expect(Product::channel($channelA)->get())->toHaveCount(0);

    $startsAt = now()->addDay();
    $endsAt = now()->addDays(2);

    $productA->channels()->syncWithPivotValues([$channelA->id], [
        'starts_at' => $startsAt,
        'enabled' => true,
        'ends_at' => $endsAt,
    ]);

    expect(Product::channel($channelA)->get())->toHaveCount(0);

    expect(Product::channel($channelA, $startsAt, $endsAt)->get())->toHaveCount(1);
});
