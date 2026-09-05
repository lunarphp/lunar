<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\Tests\Upgrade\TestCase;

uses(TestCase::class);

/**
 * Isolated prefix so this stands up its own throwaway schema without touching
 * the shared lunar_* tables.
 */
const ASSOC_SORT_UPG_PREFIX = 'upgassoc_';

beforeEach(function () {
    config(['lunar.database.table_prefix' => ASSOC_SORT_UPG_PREFIX]);
});

afterEach(function () {
    Schema::dropIfExists(ASSOC_SORT_UPG_PREFIX.'product_associations');
});

function productAssociationSortMigration(): object
{
    $path = glob(dirname(__DIR__, 3).'/packages/upgrade/database/migrations/*add_product_association_sort.php');

    return require $path[0];
}

/**
 * Stand up the v1-shaped `product_associations` table: no `sort` column.
 */
function simulateV1ProductAssociationRows(): void
{
    Schema::create(ASSOC_SORT_UPG_PREFIX.'product_associations', function (Blueprint $table) {
        $table->id();
        $table->foreignId('product_parent_id');
        $table->foreignId('product_target_id');
        $table->string('type');
        $table->timestamps();
    });

    DB::table(ASSOC_SORT_UPG_PREFIX.'product_associations')->insert([
        ['id' => 1, 'product_parent_id' => 1, 'product_target_id' => 2, 'type' => 'cross-sell', 'created_at' => now(), 'updated_at' => now()],
        ['id' => 2, 'product_parent_id' => 1, 'product_target_id' => 3, 'type' => 'up-sell', 'created_at' => now(), 'updated_at' => now()],
    ]);
}

test('it adds the sort column and defaults existing rows to zero', function () {
    simulateV1ProductAssociationRows();

    productAssociationSortMigration()->up();

    $table = ASSOC_SORT_UPG_PREFIX.'product_associations';

    expect(Schema::hasColumn($table, 'sort'))->toBeTrue()
        ->and(DB::table($table)->where('sort', 0)->count())->toBe(2);
});

test('it is a no-op when the sort column already exists', function () {
    simulateV1ProductAssociationRows();

    productAssociationSortMigration()->up();

    $before = DB::table(ASSOC_SORT_UPG_PREFIX.'product_associations')->orderBy('id')->get();

    productAssociationSortMigration()->up();

    expect(DB::table(ASSOC_SORT_UPG_PREFIX.'product_associations')->orderBy('id')->get())->toEqual($before);
});
