<?php

namespace Lunar\Hub\Tests\Unit\Http\Livewire\Components\Products\ProductTypes;

use Lunar\Hub\Http\Livewire\Components\Products\ProductTypes\ProductTypeCreate;
use Lunar\Hub\Models\Staff;
use Lunar\Hub\Tests\TestCase;
use Lunar\Models\Attribute;
use Lunar\Models\Currency;
use Lunar\Models\Language;
use Lunar\Models\Product;
use Lunar\Models\ProductType;
use Lunar\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

/**
 * @group hub.product-types
 */
class ProductTypeCreateTest extends TestCase
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
            'default' => true,
        ]);
    }

    /** @test  */
    public function component_mounts_correctly()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        LiveWire::actingAs($staff, 'staff')
            ->test(ProductTypeCreate::class)
            ->assertSeeHtml('Create product type');
    }

    /** @test  */
    public function component_has_system_attributes_preselected()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        Attribute::factory(2)->create([
            'attribute_type' => Product::class,
            'system'         => true,
        ]);

        Attribute::factory(2)->create([
            'system' => false,
        ]);

        LiveWire::actingAs($staff, 'staff')
            ->test(ProductTypeCreate::class)
            ->assertCount('selectedProductAttributes', Attribute::system(Product::class)->count());
    }

    /**
     * @test
     * @group foo
     * */
    public function can_populate_product_type_data_and_attributes()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        $attribute = Attribute::factory()->create([
            'handle' => 'new-attribute',
        ]);

        $variantAttribute = Attribute::factory()->create([
            'handle'         => 'variant-attribute',
            'attribute_type' => ProductVariant::class,
        ]);

        LiveWire::actingAs($staff, 'staff')
            ->test(ProductTypeCreate::class)
            ->assertCount('selectedProductAttributes', 0)
            ->call('addAttribute', $attribute->id, 'products')
            ->assertCount('selectedProductAttributes', 1)
            ->assertCount('selectedVariantAttributes', 0)
            ->call('addAttribute', $variantAttribute->id, 'variants')
            ->assertCount('selectedVariantAttributes', 1)
            ->set('productType.name', 'Foobar')
            ->call('create');

        $this->assertDatabaseHas((new ProductType())->getTable(), [
            'name' => 'Foobar',
        ]);

        $tablePrefix = config('lunar.database.table_prefix');

        $productType = ProductType::whereName('Foobar')->first();

        $this->assertDatabaseHas("{$tablePrefix}attributables", [
            'attributable_id'   => $productType->id,
            'attributable_type' => ProductType::class,
            'attribute_id'      => $attribute->id,
        ]);
    }
}
