<?php

namespace GetCandy\Tests\Unit\Traits;

use GetCandy\Base\StandardMediaConversions;
use GetCandy\Models\Product;
use GetCandy\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Http\UploadedFile;

/**
 * @group traits
 */
class HasMediaTraitTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function conversions_are_loaded()
    {
        $conversions = config('getcandy.media.conversions');

        $this->assertCount(1, $conversions);

        $this->assertEquals(StandardMediaConversions::class, $conversions[0]);

        $file = UploadedFile::fake()->image('avatar.jpg');

        $product = Product::factory()->create();

        $product->addMedia($file)->toMediaCollection('tests');

        $media = $product->media->first();

        $this->assertTrue($media->hasGeneratedConversion('small'));
        $this->assertTrue($media->hasGeneratedConversion('medium'));
        $this->assertTrue($media->hasGeneratedConversion('large'));
        $this->assertTrue($media->hasGeneratedConversion('zoom'));
    }
}
