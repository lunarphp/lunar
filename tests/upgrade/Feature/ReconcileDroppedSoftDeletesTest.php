<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\Tests\Upgrade\TestCase;

uses(TestCase::class);

/**
 * Isolated prefix so this stands up its own throwaway schema without touching the
 * shared lunar_* tables.
 */
const SD_UPG_PREFIX = 'upgsd_';

beforeEach(function () {
    config(['lunar.database.table_prefix' => SD_UPG_PREFIX]);
});

afterEach(function () {
    foreach (['products', 'product_variants', 'collections', 'channels'] as $table) {
        Schema::dropIfExists(SD_UPG_PREFIX.$table);
    }
});

function reconcileSoftDeletesMigration(): object
{
    $path = glob(dirname(__DIR__, 3).'/packages/upgrade/database/migrations/*reconcile_dropped_soft_deletes.php');

    return require $path[0];
}

/**
 * Stand up the four v1-shaped tables as they exist by this point in the upgrade:
 * each still carries the v1 `deleted_at` column alongside its v2 "hidden" column —
 * products/collections/channels a `status`, variants the `enabled` added by 000012.
 */
function simulateV1SoftDeletableTables(): void
{
    Schema::create(SD_UPG_PREFIX.'products', function (Blueprint $table) {
        $table->id();
        $table->string('status')->default('published');
        $table->softDeletes();
    });
    Schema::create(SD_UPG_PREFIX.'product_variants', function (Blueprint $table) {
        $table->id();
        $table->boolean('enabled')->default(true);
        $table->softDeletes();
    });
    Schema::create(SD_UPG_PREFIX.'collections', function (Blueprint $table) {
        $table->id();
        $table->string('status')->default('published');
        $table->softDeletes();
    });
    Schema::create(SD_UPG_PREFIX.'channels', function (Blueprint $table) {
        $table->id();
        $table->string('status')->default('active');
        $table->softDeletes();
    });
}

test('it maps every v1 soft-deleted row onto the v2 hidden state and drops deleted_at', function () {
    simulateV1SoftDeletableTables();

    $deleted = '2024-01-01 00:00:00';

    DB::table(SD_UPG_PREFIX.'products')->insert([
        ['id' => 1, 'status' => 'published', 'deleted_at' => null],
        ['id' => 2, 'status' => 'published', 'deleted_at' => $deleted],
    ]);
    DB::table(SD_UPG_PREFIX.'product_variants')->insert([
        ['id' => 1, 'enabled' => true, 'deleted_at' => null],
        ['id' => 2, 'enabled' => true, 'deleted_at' => $deleted],
    ]);
    DB::table(SD_UPG_PREFIX.'collections')->insert([
        ['id' => 1, 'status' => 'published', 'deleted_at' => null],
        ['id' => 2, 'status' => 'published', 'deleted_at' => $deleted],
    ]);
    DB::table(SD_UPG_PREFIX.'channels')->insert([
        ['id' => 1, 'status' => 'active', 'deleted_at' => null],
        ['id' => 2, 'status' => 'active', 'deleted_at' => $deleted],
    ]);

    reconcileSoftDeletesMigration()->up();

    // The orphaned v1 column is gone everywhere, matching a fresh v2 install.
    foreach (['products', 'product_variants', 'collections', 'channels'] as $table) {
        expect(Schema::hasColumn(SD_UPG_PREFIX.$table, 'deleted_at'))->toBeFalse();
    }

    // Soft-deleted rows are hidden via the v2 mechanism; live rows are untouched.
    expect(DB::table(SD_UPG_PREFIX.'products')->find(1)->status)->toBe('published')
        ->and(DB::table(SD_UPG_PREFIX.'products')->find(2)->status)->toBe('archived')
        ->and((bool) DB::table(SD_UPG_PREFIX.'product_variants')->find(1)->enabled)->toBeTrue()
        ->and((bool) DB::table(SD_UPG_PREFIX.'product_variants')->find(2)->enabled)->toBeFalse()
        ->and(DB::table(SD_UPG_PREFIX.'collections')->find(1)->status)->toBe('published')
        ->and(DB::table(SD_UPG_PREFIX.'collections')->find(2)->status)->toBe('archived')
        ->and(DB::table(SD_UPG_PREFIX.'channels')->find(1)->status)->toBe('active')
        ->and(DB::table(SD_UPG_PREFIX.'channels')->find(2)->status)->toBe('inactive');
});

test('it is a no-op on a fresh v2 table that never had deleted_at', function () {
    Schema::create(SD_UPG_PREFIX.'product_variants', function (Blueprint $table) {
        $table->id();
        $table->boolean('enabled')->default(true);
    });
    DB::table(SD_UPG_PREFIX.'product_variants')->insert(['id' => 1, 'enabled' => true]);

    reconcileSoftDeletesMigration()->up();

    expect((bool) DB::table(SD_UPG_PREFIX.'product_variants')->find(1)->enabled)->toBeTrue();
});
