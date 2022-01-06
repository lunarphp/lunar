<?php

namespace GetCandy\Tests\Unit\Models;

use GetCandy\Models\Address;
use GetCandy\Models\Country;
use GetCandy\Models\Customer;
use GetCandy\Models\Language;
use GetCandy\Models\Product;
use GetCandy\Models\Url;
use GetCandy\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

/**
 * @group models
 * @group urls
 */
class UrlTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_create_a_url()
    {
        $product = Product::factory()->create();
        $language = Language::factory()->create();

        $data = [
            'language_id' => $language->id,
            'element_id' => $product->id,
            'element_type' => Product::class,
            'slug' => Str::slug($product->translateAttribute('name')),
            'default' => true,
        ];

        Url::create($data);

        $this->assertDatabaseHas('getcandy_urls', $data);
    }

    /** @test */
    public function can_fetch_element_from_url_relationship()
    {
        $product = Product::factory()->create();
        $language = Language::factory()->create();

        $data = [
            'language_id' => $language->id,
            'element_id' => $product->id,
            'element_type' => Product::class,
            'slug' => Str::slug($product->translateAttribute('name')),
            'default' => true,
        ];

        $url = Url::create($data);

        $this->assertInstanceOf(Product::class, $url->element);
        $this->assertEquals($product->id, $url->element->id);
    }
}
