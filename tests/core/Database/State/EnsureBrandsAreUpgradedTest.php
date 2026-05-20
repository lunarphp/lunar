<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Lunar\Core\Facades\DB;
use Lunar\Core\FieldTypes\Text;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductType;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

// Exercises a v1.x → v2.x state transition that no longer applies to the
// flattened v2 baseline (brands table + products.brand_id ship from day 1).
// Coverage for the upgrade path moves to packages/upgrade tests under spec 0001.
test('can run', function () {
    $this->markTestSkipped('Obsolete after spec 0003 flat baseline. Moves to upgrade package tests.');
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
