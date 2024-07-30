<?php

uses(\Lunar\Tests\Core\TestCase::class);

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Lunar\Facades\DB;
use Lunar\FieldTypes\Text;
use Lunar\Models\Brand;
use Lunar\Models\Language;
use Lunar\Models\Product;
use Lunar\Models\ProductType;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can run', function () {
    Storage::fake('local');

    Language::factory()->create([
        'default' => true,
    ]);

    $prefix = config('lunar.database.table_prefix');
    Schema::dropIfExists("{$prefix}brands");

    Schema::table("{$prefix}products", function ($table) {
        if (can_drop_foreign_keys()) {
            $table->dropForeign(['brand_id']);
        }
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

    $pa = Product::forceCreate([
        'brand' => 'Brand A',
        'product_type_id' => $productType->id,
        'status' => 'published',
        'attribute_data' => collect([
            'name' => new Text('Product A'),
        ]),
    ]);

    $pb = Product::forceCreate([
        'brand' => 'Brand A',
        'product_type_id' => $productType->id,
        'status' => 'published',
        'attribute_data' => collect([
            'name' => new Text('Product B'),
        ]),
    ]);

    $pc = Product::forceCreate([
        'brand' => 'Brand B',
        'product_type_id' => $productType->id,
        'status' => 'published',
        'attribute_data' => collect([
            'name' => new Text('Product C'),
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

    expect(Product::whereBrandId($brandA->id)->get())->toHaveCount(2)
        ->and(Product::whereBrandId($brandB->id)->get())->toHaveCount(1);
});
