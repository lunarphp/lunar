<?php

namespace Lunar\Tests\Unit\Console;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\Channel;
use Lunar\Models\Product;
use Lunar\Tests\TestCase;

/**
 * @group lunar.traits
 */
class HasChannelsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_scope_results_to_a_channel()
    {
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

        $this->assertCount(1, $resultA);
        $this->assertCount(1, $resultB);
        $this->assertCount(2, $resultC);

        $this->assertEquals($productA->id, $resultA->first()->id);
        $this->assertEquals($productB->id, $resultB->first()->id);

        $productA->channels()->syncWithPivotValues([$channelA->id], [
            'starts_at' => now(),
            'enabled' => false,
            'ends_at' => now()->addDay(),
        ]);

        $this->assertCount(0, Product::channel($channelA)->get());

        $productA->channels()->syncWithPivotValues([$channelA->id], [
            'starts_at' => null,
            'enabled' => true,
            'ends_at' => now()->addDay(),
        ]);

        $this->assertCount(1, Product::channel($channelA)->get());

        $productA->channels()->syncWithPivotValues([$channelA->id], [
            'starts_at' => now()->subDay(),
            'enabled' => true,
            'ends_at' => now()->subHour(),
        ]);

        $this->assertCount(0, Product::channel($channelA)->get());


        $startsAt = now()->addDay();
        $endsAt = now()->addDays(2);

        $productA->channels()->syncWithPivotValues([$channelA->id], [
            'starts_at' => $startsAt,
            'enabled' => true,
            'ends_at' => $endsAt,
        ]);

        $this->assertCount(0, Product::channel($channelA)->get());

        $this->assertCount(1, Product::channel($channelA, $startsAt, $endsAt)->get());
    }
}
