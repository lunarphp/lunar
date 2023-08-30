<?php

namespace Lunar\Tests\Unit\Base;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Lunar\Base\AttributeManifest;
use Lunar\Base\AttributeManifestInterface;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Models\Attribute;
use Lunar\Models\Channel;
use Lunar\Models\Product;
use Lunar\Tests\TestCase;

/**
 * @group core.attribute-manifest
 */
class AttributeManifestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_instantiate_class()
    {
        $manifest = app(AttributeManifestInterface::class);

        $this->assertInstanceOf(AttributeManifest::class, $manifest);
    }

    /** @test */
    public function can_return_types()
    {
        $manifest = app(AttributeManifestInterface::class);

        $this->assertInstanceOf(Collection::class, $manifest->getTypes());
    }

    /** @test */
    public function has_base_types_set()
    {
        $manifest = app(AttributeManifestInterface::class);

        $this->assertInstanceOf(Collection::class, $manifest->getTypes());

        $this->assertNotEmpty($manifest->getTypes());
    }

    /** @test */
    public function can_add_type()
    {
        $manifest = app(AttributeManifestInterface::class);

        $manifest->addType(Channel::class);

        $this->assertNotNull($manifest->getType('channel'));
    }

    /** @test */
    public function can_get_searchable_attributes()
    {
        $attributeA = Attribute::factory()->create([
            'attribute_type' => Product::class,
            'searchable' => true,
        ]);
        $attributeB = Attribute::factory()->create([
            'attribute_type' => Product::class,
            'searchable' => true,
        ]);
        Attribute::factory()->create([
            'attribute_type' => \Lunar\Models\Collection::class,
            'searchable' => false,
        ]);
        $attributeD = Attribute::factory()->create([
            'attribute_type' => \Lunar\Models\Collection::class,
            'type' => TranslatedText::class,
            'searchable' => true,
        ]);

        $manifest = app(AttributeManifestInterface::class);

        $productAttributes = $manifest->getSearchableAttributes(Product::class);
        $collectionAttributes = $manifest->getSearchableAttributes(\Lunar\Models\Collection::class);

        $this->assertCount(2, $productAttributes);
        $this->assertSame([$attributeA->handle, $attributeB->handle], $productAttributes->pluck('handle')->toArray());
        $this->assertCount(1, $collectionAttributes);
        $this->assertSame([$attributeD->handle], $collectionAttributes->pluck('handle')->toArray());
    }
}
