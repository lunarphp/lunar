<?php

namespace GetCandy\Hub\Tests\Unit\Http\Livewire\Components\Products\ProductTypes;

use GetCandy\Hub\Http\Livewire\Components\Products\ProductTypes\ProductTypeCreate;
use GetCandy\Hub\Models\Staff;
use GetCandy\Hub\Tests\TestCase;
use GetCandy\Models\Attribute;
use GetCandy\Models\Currency;
use GetCandy\Models\Language;
use GetCandy\Models\Product;
use GetCandy\Models\ProductType;
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
            'attribute_type' => ProductType::class,
            'system'         => true,
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
            ->assertCount('selectedAttributes', Attribute::system(ProductType::class)->count());
    }

    /** @test  */
    public function can_populate_product_type_data_and_attributes()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        $attribute = Attribute::factory()->create([
            'handle' => 'new-attribute',
        ]);

        LiveWire::actingAs($staff, 'staff')
            ->test(ProductTypeCreate::class)
            ->assertCount('selectedAttributes', 0)
            ->call('addAttribute', $attribute->id)
            ->assertCount('selectedAttributes', 1)
            ->set('productType.name', 'Foobar')
            ->call('create');

        $this->assertDatabaseHas((new ProductType())->getTable(), [
            'name' => 'Foobar',
        ]);

        $tablePrefix = config('getcandy.database.table_prefix');

        $productType = ProductType::whereName('Foobar')->first();

        $this->assertDatabaseHas("{$tablePrefix}attributables", [
            'attributable_id'   => $productType->id,
            'attributable_type' => ProductType::class,
            'attribute_id'      => $attribute->id,
        ]);
    }
}
