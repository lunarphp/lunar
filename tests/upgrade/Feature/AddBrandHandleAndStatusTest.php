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
const BRAND_UPG_PREFIX = 'upgbrand_';

beforeEach(function () {
    config(['lunar.database.table_prefix' => BRAND_UPG_PREFIX]);
});

afterEach(function () {
    Schema::dropIfExists(BRAND_UPG_PREFIX.'brands');
});

function brandHandleMigration(): object
{
    $path = glob(dirname(__DIR__, 3).'/packages/upgrade/database/migrations/*add_brand_handle_and_status.php');

    return require $path[0];
}

/**
 * Stand up the v1-shaped `brands` table: no `handle` and no `status`.
 */
function simulateV1BrandRows(): void
{
    Schema::create(BRAND_UPG_PREFIX.'brands', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });

    DB::table(BRAND_UPG_PREFIX.'brands')->insert([
        ['id' => 1, 'name' => 'Stark Industries', 'created_at' => now(), 'updated_at' => now()],
        ['id' => 2, 'name' => 'Stark  Industries', 'created_at' => now(), 'updated_at' => now()],
        ['id' => 3, 'name' => 'Wayne Enterprises', 'created_at' => now(), 'updated_at' => now()],
    ]);
}

test('it adds the columns and backfills unique handles and active status', function () {
    simulateV1BrandRows();

    brandHandleMigration()->up();

    $table = BRAND_UPG_PREFIX.'brands';

    expect(Schema::hasColumn($table, 'handle'))->toBeTrue()
        ->and(Schema::hasColumn($table, 'status'))->toBeTrue();

    $rows = DB::table($table)->orderBy('id')->get();

    // Both Stark rows slug identically; the second gets a suffix.
    expect($rows[0]->handle)->toBe('stark-industries')
        ->and($rows[1]->handle)->toBe('stark-industries-2')
        ->and($rows[2]->handle)->toBe('wayne-enterprises')
        ->and($rows->pluck('status')->unique()->all())->toBe(['active']);
});

test('it is a no-op when the handle column already exists', function () {
    simulateV1BrandRows();

    brandHandleMigration()->up();

    $before = DB::table(BRAND_UPG_PREFIX.'brands')->orderBy('id')->get();

    brandHandleMigration()->up();

    expect(DB::table(BRAND_UPG_PREFIX.'brands')->orderBy('id')->get())->toEqual($before);
});
