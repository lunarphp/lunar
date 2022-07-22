<?php

namespace GetCandy\Hub\Tests\Unit\Http\Livewire\Components\Settings\Attributes;

use GetCandy\Hub\Http\Livewire\Components\Settings\Attributes\AttributeGroupEdit;
use GetCandy\Hub\Http\Livewire\Components\Settings\Attributes\AttributeShow;
use GetCandy\Hub\Tests\TestCase;
use GetCandy\Models\AttributeGroup;
use GetCandy\Models\Collection;
use GetCandy\Models\CollectionGroup;
use GetCandy\Models\Language;
use GetCandy\Models\Product;
use GetCandy\Models\ProductOption;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

class AttributeGroupModelCreateTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        Language::factory()->create([
            'default' => true,
            'code'    => 'en',
        ]);
    }

    /** @test */
    public function can_create_product_options_group()
    {
        ProductOption::factory()->create(['name' => 'Sizes']);
        ProductOption::factory()->create(['name' => 'Color']);

        $data = [
            'type'     => 'model',
            'source'   => ProductOption::class,
            'name'     => 'Product Options',
            'handle'   => 'product_options',
            'position' => 1,
        ];

        Livewire::test(AttributeGroupEdit::class, [
            'typeHandle' => 'product',
            'attributableType' => Product::class,
        ])
            ->set('attributeGroup.name.en', $data['name'])
            ->set('attributeGroup.type', $data['type'])
            ->set('attributeGroup.source', $data['source'])
            ->call('create');

        $data['name'] = collect(['en' => $data['name']])->toJson();
        $this->assertDatabaseHas((new AttributeGroup)->getTable(), $data);
    }

    /** @test */
    public function can_create_brands_collection_group()
    {
        CollectionGroup::factory()->create([
            'name'   => 'Brands',
            'handle' => 'brands',
        ]);

        $data = [
            'type'     => 'model',
            'source'   => Collection::class,
            'name'     => 'Brands',
            'handle'   => 'brands',
            'position' => 1,
        ];

        Livewire::test(AttributeGroupEdit::class, [
            'typeHandle' => 'product',
            'attributableType' => Product::class,
        ])
            ->set('attributeGroup.name.en', $data['name'])
            ->set('attributeGroup.type', $data['type'])
            ->set('attributeGroup.source', $data['source'])
            ->assertSee('Select Collection')
            ->assertSee('Brands')
            ->call('create');

        $data['name'] = collect(['en' => $data['name']])->toJson();
        $this->assertDatabaseHas((new AttributeGroup)->getTable(), $data);
    }

    /** @test */
    public function can_see_product_options_on_mount()
    {
        // Run the previous test 1st to create the product options group
        $this->can_create_product_options_group();

        Livewire::test(AttributeShow::class, ['type' => 'product'])
            ->assertSee('Product Options')
            ->assertSeeTextInOrder(ProductOption::pluck('name')->toArray());
    }

    /** @test */
    public function can_see_product_options_on_update()
    {
        // Run the previous test 1st to create the product options group
        $this->can_create_product_options_group();

        Livewire::test(AttributeShow::class, ['type' => 'product'])
                ->call('refreshGroups')
                ->assertSee('Product Options')
                ->assertSeeTextInOrder(ProductOption::pluck('name')->toArray());
    }
}
