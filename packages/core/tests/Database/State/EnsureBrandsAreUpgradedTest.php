<?php

namespace Lunar\Tests\Database\State;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\FieldTypes\Text;
use Lunar\Models\Brand;
use Lunar\Models\Product;
use Lunar\Models\ProductType;
use Lunar\Tests\TestCase;

/**
 * @group database.state
 */
class EnsureBrandsAreUpgradedTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_run()
    {
        $prefix = config('lunar.database.table_prefix');
        Schema::dropIfExists("{$prefix}brands");

        Schema::table("{$prefix}products", function ($table) {
            $table->dropColumn('brand_id');
        });

        Schema::table("{$prefix}products", function ($table) {
            $table->string('brand')->nullable();
        });

        DB::table('migrations')->whereIn('migration', [
            '2022_08_09_100001_create_brands_table',
            '2022_08_09_100002_add_brand_id_to_products_table',
        ])->delete();

        $productType = ProductType::factory()->create();

        Product::forceCreate([
            'brand' => 'Brand A',
            'product_type_id' => $productType->id,
            'status'          => 'published',
            'attribute_data'  => collect([
                'name'        => new Text('Product A'),
            ]),
        ]);

        Product::forceCreate([
            'brand' => 'Brand A',
            'product_type_id' => $productType->id,
            'status'          => 'published',
            'attribute_data'  => collect([
                'name'        => new Text('Product B'),
            ]),
        ]);

        Product::forceCreate([
            'brand' => 'Brand B',
            'product_type_id' => $productType->id,
            'status'          => 'published',
            'attribute_data'  => collect([
                'name'        => new Text('Product C'),
            ]),
        ]);

        $this->assertDatabaseHas((new Product)->getTable(), [
            'brand' => 'Brand A',
        ]);

        $this->artisan('migrate');

        $this->assertDatabaseHas((new Brand)->getTable(), [
            'name' => 'Brand A',
        ]);

        $this->assertDatabaseHas((new Brand)->getTable(), [
            'name' => 'Brand B',
        ]);

        $brandA = Brand::whereName('Brand A')->first();
        $brandB = Brand::whereName('Brand B')->first();

        $this->assertCount(2, Product::whereBrandId($brandA->id)->get());
        $this->assertCount(1, Product::whereBrandId($brandB->id)->get());
    }
}
