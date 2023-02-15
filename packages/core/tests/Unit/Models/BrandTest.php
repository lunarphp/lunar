<?php

namespace Lunar\Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Lunar\Generators\UrlGenerator;
use Lunar\Models\Brand;
use Lunar\Models\Language;
use Lunar\Models\Url;
use Lunar\Tests\TestCase;

class BrandTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_make_a_brand()
    {
        $brand = Brand::factory()->create([
            'name' => 'Test Brand',
        ]);
        $this->assertEquals('Test Brand', $brand->name);
    }

    /** @test */
    public function can_make_brand_with_slug()
    {
        Config::set('lunar.urls.generator', UrlGenerator::class);

        Language::factory()->create(['default' => true]);

        $brand = Brand::factory()->create([
            'name' => 'Test Brand',
        ]);

        $this->assertDatabaseHas((new Url)->getTable(), [
            'element_type' => Brand::class,
            'element_id' => $brand->id,
            'slug' => Str::slug($brand->name),
        ]);

        $this->assertCount(1, $brand->refresh()->urls);
    }
}
