<?php

namespace Lunar\Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Lunar\Generators\UrlGenerator;
use Lunar\Models\Brand;
use Lunar\Models\Language;
use Lunar\Models\Url;
use Lunar\Tests\TestCase;

/**
 * @group lunar.brands
 */

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
    public function can_generate_url()
    {
        Config::set('lunar.urls.generator', UrlGenerator::class);

        Language::factory()->create([
            'default' => true,
        ]);

        $brand = Brand::factory()->create([
            'name' => 'Test Brand',
        ]);

        $this->assertDatabaseHas((new Url)->getTable(), [
            'slug' => 'test-brand',
            'element_type' => Brand::class,
            'element_id' => $brand->id,
        ]);
    }
}
