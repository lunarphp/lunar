<?php

namespace Lunar\Tests\Unit\Traits;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Lunar\Base\StandardMediaCollections;
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
        $conversions = config('lunar.media.collections');

        $this->assertCount(1, $conversions);

        $this->assertEquals(StandardMediaCollections::class, $conversions[0]);

        $file = UploadedFile::fake()->image('avatar.jpg');

        $product = Product::factory()->create();

        $product->addMedia($file)->toMediaCollection('images');

        $image = $product->images->first();

        $this->assertTrue($image->hasGeneratedConversion('small'));
        $this->assertTrue($image->hasGeneratedConversion('medium'));
        $this->assertTrue($image->hasGeneratedConversion('large'));
        $this->assertTrue($image->hasGeneratedConversion('zoom'));
    }

    /** @test */
    public function images_can_have_fallback_url()
    {
        $testImageUrl = 'https://picsum.photos/200';
        config()->set('lunar.media.fallback.url', $testImageUrl);

        $product = Product::factory()->create();

        $this->assertEquals($product->getFirstMediaUrl('images'), $testImageUrl);
    }

    /** @test */
    public function images_can_have_fallback_path()
    {
        $testImagePath = public_path('test.jpg');
        config()->set('lunar.media.fallback.path', $testImagePath);

        $product = Product::factory()->create();

        $this->assertEquals($product->getFirstMediaPath('images'), $testImagePath);
    }
}
