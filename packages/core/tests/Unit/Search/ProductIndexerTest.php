<?php

namespace Lunar\Tests\Unit\Search;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\FieldTypes\Text;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Models\Attribute;
use Lunar\Models\Language;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;
use Lunar\Search\ProductIndexer;
use Lunar\Tests\TestCase;

/**
 * @group lunar.search
 * @group lunar.search.product
 */
class ProductIndexerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_return_correct_searchable_data()
    {
        Language::factory()->create([
            'code' => 'en',
            'default' => true,
        ]);

        Language::factory()->create([
            'code' => 'dk',
            'default' => false,
        ]);

        $attributeA = Attribute::factory()->create([
            'attribute_type' => Product::class,
            'searchable' => true,
        ]);
        $attributeB = Attribute::factory()->create([
            'attribute_type' => Product::class,
            'searchable' => true,
        ]);
        $attributeC = Attribute::factory()->create([
            'attribute_type' => Product::class,
            'searchable' => false,
        ]);
        $attributeD = Attribute::factory()->create([
            'attribute_type' => Product::class,
            'type' => TranslatedText::class,
            'searchable' => true,
        ]);

        $product = Product::factory()->create([
            'attribute_data' => collect([
                $attributeA->handle => new Text('Attribute A'),
                $attributeB->handle => new Text('Attribute B'),
                $attributeC->handle => new Text('Attribute C'),
                $attributeD->handle => new TranslatedText([
                    'en' => 'Attribute D EN',
                    'dk' => 'Attribute D DK',
                ]),
            ]),
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
        ]);

        $data = app(ProductIndexer::class)->toSearchableArray($product);
        $this->assertArrayHasKey('id', $data);
        $this->assertSame([$variant->sku], $data['skus']);
        $this->assertEquals($product->status, $data['status']);
        $this->assertEquals($product->productType->name, $data['product_type']);
        $this->assertEquals($product->brand?->name, $data['brand']);
        $this->assertArrayHasKey($attributeA->handle, $data);
        $this->assertArrayHasKey($attributeB->handle, $data);
        $this->assertArrayNotHasKey($attributeC->handle, $data);
        $this->assertArrayHasKey($attributeD->handle.'_en', $data);
        $this->assertArrayHasKey($attributeD->handle.'_dk', $data);
    }
}
