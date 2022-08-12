<?php

namespace GetCandy\Tests\Unit\Traits;

use GetCandy\Generators\UrlGenerator;
use GetCandy\Models\Language;
use GetCandy\Models\Product;
use GetCandy\Models\Url;
use GetCandy\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

/**
 * @group traits
 */
class HasUrlsTraitTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function urls_dont_generate_by_default()
    {
        $product = Product::factory()->create();

        $this->assertCount(0, $product->refresh()->urls);

        $this->assertDatabaseMissing((new Url)->getTable(), [
            'element_type' => Product::class,
            'element_id' => $product->id,
        ]);
    }

    /** @test
     * public function can_generate_urls()
     * {
     * Language::factory()->create(['default' => true]);
     *
     * Config::set('getcandy.urls.generator', UrlGenerator::class);
     *
     * $product = Product::factory()->create();
     *
     * $this->assertCount(1, $product->refresh()->urls);
     *
     * $this->assertDatabaseHas((new Url)->getTable(), [
     * 'element_type' => Product::class,
     * 'element_id' => $product->id,
     * 'slug' => Str::slug($product->translateAttribute('name')),
     * ]);
     * }*/
}
