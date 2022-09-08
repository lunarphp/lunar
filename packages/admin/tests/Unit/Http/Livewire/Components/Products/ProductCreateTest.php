<?php

namespace GetCandy\Hub\Tests\Unit\Http\Livewire\Components\Products;

use GetCandy\Hub\Http\Livewire\Components\Products\ProductCreate;
use GetCandy\Hub\Models\Staff;
use GetCandy\Hub\Tests\TestCase;
use GetCandy\Models\Collection;
use GetCandy\Models\Currency;
use GetCandy\Models\Language;
use GetCandy\Models\Price;
use GetCandy\Models\Product;
use GetCandy\Models\ProductAssociation;
use GetCandy\Models\ProductType;
use GetCandy\Models\ProductVariant;
use GetCandy\Models\TaxClass;
use GetCandy\Models\Url;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

/**
 * @group hub.products
 */
class ProductCreateTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        Language::factory()->create([
            'default' => true,
            'code'    => 'en',
        ]);

        Language::factory()->create([
            'default' => false,
            'code'    => 'fr',
        ]);

        Currency::factory()->create([
            'default'        => true,
            'decimal_places' => 2,
        ]);

        TaxClass::factory()->create([
            'default' => true,
        ]);

        ProductType::factory()->create();
    }

    /** @test  */
    public function component_mounts_correctly()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        LiveWire::actingAs($staff, 'staff')
            ->test(ProductCreate::class)
            ->assertViewIs('adminhub::livewire.components.products.create');
    }

    /** @test */
    public function can_create_product()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        $currency = Currency::getDefault();

        $language = Language::getDefault();

        $productB = Product::factory()->create([
            'status' => 'published',
            'brand'  => 'PROB',
        ]);

        $productC = Product::factory()->create([
            'status' => 'published',
            'brand'  => 'PROC',
        ]);

        $collection = Collection::factory()->create();

        $this->assertDatabaseMissing((new Product)->collections()->getTable(), [
            'collection_id' => $collection->id,
        ]);

        $component = LiveWire::actingAs($staff, 'staff')
            ->test(ProductCreate::class)
            ->set('variant.sku', '1234')
            ->set('variant.tax_ref', 'CUSTOMTAX')
            ->set("basePrices.{$currency->code}.price", 1234)
            ->call('addUrl')
            ->set('urls.0.slug', 'foo-bar')
            ->set('associations', collect([
                [
                    'inverse' => false,
                    'target_id' => $productB->id,
                    'thumbnail' => optional($productB->thumbnail)->getUrl('small'),
                    'name' => $productB->translateAttribute('name'),
                    'type' => 'cross-sell',
                ],
                [
                    'inverse' => true,
                    'target_id' => $productC->id,
                    'thumbnail' => optional($productC->thumbnail)->getUrl('small'),
                    'name' => $productC->translateAttribute('name'),
                    'type' => 'cross-sell',
                ],
            ]))->set('collections', collect([[
                'id' => $collection->id,
                'name' => $collection->translateAttribute('name'),
                'group_id' => $collection->collection_group_id,
                'group_name' => $collection->group->name,
                'thumbnail' => null,
                'breadcrumb' => ['Foo', 'Bar'],
                'position' => 1,
            ]]))
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas((new ProductAssociation)->getTable(), [
            'product_target_id' => $productB->id,
            'product_parent_id' => $component->get('product.id'),
            'type' => 'cross-sell',
        ]);

        $this->assertDatabaseHas((new ProductAssociation)->getTable(), [
            'product_parent_id' => $productC->id,
            'product_target_id' => $component->get('product.id'),
            'type' => 'cross-sell',
        ]);

        $this->assertDatabaseHas((new Product)->collections()->getTable(), [
            'collection_id' => $collection->id,
            'product_id' => $component->get('product.id'),
        ]);

        $this->assertDatabaseHas((new ProductVariant)->getTable(), [
            'sku' => '1234',
            'tax_ref' => 'CUSTOMTAX',
        ]);

        $this->assertDatabaseHas((new Price)->getTable(), [
            'price' => '123400',
        ]);

        $this->assertDatabaseCount((new Price)->getTable(), 1);

        $this->assertDatabaseHas((new Url)->getTable(), [
            'slug' => 'foo-bar',
            'element_type' => Product::class,
            'element_id' => $component->get('product.id'),
        ]);
    }
}
