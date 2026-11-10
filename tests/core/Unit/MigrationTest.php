<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Lunar\Models\Product;
use Lunar\Models\ProductOption;
use Lunar\Tests\Core\TestCase;

use function Pest\Laravel\artisan;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

uses(TestCase::class)->group('migrations');

test('all migrations can run rollback', function () {
    artisan('migrate');

    $migrationsList = collect(File::allFiles(
        __DIR__.'/../../../packages/core/database/migrations'
    ))->map(fn ($file) => pathinfo($file->getFilename(), PATHINFO_FILENAME));

    foreach ($migrationsList as $migration) {
        assertDatabaseHas('migrations', [
            'migration' => $migration,
        ]);
    }

    artisan('migrate:rollback');

    // Restore the schema for subsequent tests — RefreshDatabase only migrates
    // once per process on a file-backed connection.
    artisan('migrate');
});

test('migrations roll back and re-apply', function () {
    $migrationsList = collect(File::allFiles(
        __DIR__.'/../../../packages/core/database/migrations'
    ))->map(fn ($file) => pathinfo($file->getFilename(), PATHINFO_FILENAME));

    artisan('migrate');
    artisan('migrate:rollback');

    foreach ($migrationsList as $migration) {
        assertDatabaseMissing('migrations', ['migration' => $migration]);
    }

    artisan('migrate');

    foreach ($migrationsList as $migration) {
        assertDatabaseHas('migrations', ['migration' => $migration]);
    }
});

test('product_product_option unique migration removes duplicates', function () {
    $table = config('lunar.database.table_prefix').'product_product_option';

    artisan('migrate');
    // Roll back this specific migration — `--step 1` would silently target
    // whichever migration happens to be newest.
    artisan('migrate:rollback', [
        '--path' => realpath(__DIR__.'/../../../packages/core/database/migrations/2026_04_30_100000_add_unique_lunar_product_product_option.php'),
        '--realpath' => true,
    ]);

    expect(Schema::hasIndex($table, ['product_id', 'product_option_id'], 'unique'))->toBeFalse();

    [$productA, $productB] = Product::factory()->count(2)->create()->all();
    [$optionA, $optionB] = ProductOption::factory()->count(2)->create()->all();

    DB::table($table)->insert([
        ['product_id' => $productA->id, 'product_option_id' => $optionA->id, 'position' => 1],
        ['product_id' => $productA->id, 'product_option_id' => $optionA->id, 'position' => 2],
        ['product_id' => $productA->id, 'product_option_id' => $optionA->id, 'position' => 3],
        ['product_id' => $productA->id, 'product_option_id' => $optionB->id, 'position' => 1],
        ['product_id' => $productB->id, 'product_option_id' => $optionA->id, 'position' => 1],
    ]);

    $survivorId = DB::table($table)
        ->where('product_id', $productA->id)
        ->where('product_option_id', $optionA->id)
        ->min('id');

    artisan('migrate');

    expect(Schema::hasIndex($table, ['product_id', 'product_option_id'], 'unique'))->toBeTrue();

    $remainingPairs = DB::table($table)
        ->where('product_id', $productA->id)
        ->where('product_option_id', $optionA->id)
        ->pluck('id');

    expect($remainingPairs)->toHaveCount(1)
        ->and($remainingPairs->first())->toBe($survivorId);

    expect(DB::table($table)->count())->toBe(3);

    // Factory rows created here are committed (DDL inside the test breaks the
    // RefreshDatabase transaction), so explicitly clean them up to avoid
    // leaking into subsequent tests.
    DB::table($table)->delete();
    DB::table(config('lunar.database.table_prefix').'product_options')
        ->whereIn('id', [$optionA->id, $optionB->id])->delete();
    DB::table(config('lunar.database.table_prefix').'products')
        ->whereIn('id', [$productA->id, $productB->id])->delete();
    DB::table(config('lunar.database.table_prefix').'product_types')
        ->whereIn('id', [$productA->product_type_id, $productB->product_type_id])->delete();
    DB::table(config('lunar.database.table_prefix').'brands')
        ->whereIn('id', [$productA->brand_id, $productB->brand_id])->delete();
});
