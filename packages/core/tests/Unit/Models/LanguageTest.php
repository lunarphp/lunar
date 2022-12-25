<?php

namespace Lunar\Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Lunar\Models\Language;
use Lunar\Models\Product;
use Lunar\Models\Url;
use Lunar\Tests\TestCase;

class LanguageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_make_a_language()
    {
        $language = Language::factory()->create([
            'code' => 'fr',
            'name' => 'Français',
            'default' => true,
        ]);

        $this->assertEquals('fr', $language->code);
        $this->assertEquals('Français', $language->name);
        $this->assertTrue($language->default);
    }

    /** @test */
    public function can_delete_a_language()
    {
        $product = Product::factory()->create();
        $dutch = Language::factory()->create([
            'code' => 'fr',
            'name' => 'Français',
            'default' => true,
        ]);

        $french = Language::factory()->create([
            'code' => 'fr',
            'name' => 'Français',
            'default' => false,
        ]);

        foreach ([$dutch, $french] as $language) {
            $data = [
                'language_id' => $language->id,
                'element_id' => $product->id,
                'element_type' => Product::class,
                'slug' => Str::slug($product->translateAttribute('name')),
                'default' => true,
            ];

            Url::create($data);
        }

        $this->assertDatabaseHas('lunar_urls', [
            'language_id' => $french->id,
            'element_id' => $product->id,
            'element_type' => Product::class,
        ]);

        $french->delete();

        $this->assertDatabaseMissing('lunar_urls', [
            'language_id' => $french->id,
            'element_id' => $product->id,
            'element_type' => Product::class,
        ]);
    }
}
