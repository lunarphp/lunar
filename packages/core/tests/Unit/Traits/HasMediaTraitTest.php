<?php

namespace Lunar\Tests\Unit\Traits;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Lunar\Base\StandardMediaConversions;
use Lunar\Models\Product;
use Lunar\Tests\TestCase;

/**
 * @group traits
 */
class HasMediaTraitTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function conversions_are_loaded()
    {
        $conversions = config('lunar.media.conversions');

        $this->assertCount(1, $conversions);

        $this->assertEquals(StandardMediaConversions::class, $conversions[0]);

        $file = UploadedFile::fake()->image('avatar.jpg');

        $product = Product::factory()->create();

        $product->addMedia($file)->toMediaCollection('images');

        $image = $product->images->first();

        $this->assertTrue($image->hasGeneratedConversion('small'));
        $this->assertTrue($image->hasGeneratedConversion('medium'));
        $this->assertTrue($image->hasGeneratedConversion('large'));
        $this->assertTrue($image->hasGeneratedConversion('zoom'))
    }
}
