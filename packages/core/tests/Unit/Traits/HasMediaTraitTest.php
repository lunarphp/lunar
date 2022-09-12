<?php

namespace GetCandy\Tests\Unit\Traits;

use GetCandy\Base\StandardMediaConversions;
use GetCandy\Models\Product;
use GetCandy\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        $product->addMedia($file)->toMediaCollection('images');

        $image = $product->images->first();

        $this->assertTrue($image->hasGeneratedConversion('small'));
        $this->assertTrue($image->hasGeneratedConversion('medium'));
        $this->assertTrue($image->hasGeneratedConversion('large'));
        $this->assertTrue($image->hasGeneratedConversion('zoom'));
    }
}
