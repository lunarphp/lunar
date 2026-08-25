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

function upgradeMigration(string $glob): object
{
    $path = glob(dirname(__DIR__, 3).'/packages/upgrade/database/migrations/'.$glob);

    return require $path[0];
}

/**
 * Stand up the tables in their TRUE v1 shape at the point these steps run (after
 * 000012 added `product_variants.enabled`): products carry a free-form v1 `status`,
 * variants carry `enabled`, and collections/channels carry ONLY `deleted_at` — no
 * `status` column, because v1 never had one and no earlier upgrade step adds it.
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
        $table->softDeletes();
    });
    Schema::create(SD_UPG_PREFIX.'channels', function (Blueprint $table) {
        $table->id();
        $table->softDeletes();
    });
}

test('the add-status then reconcile chain maps every soft-delete onto the v2 hidden state and drops deleted_at', function () {
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
        ['id' => 1, 'deleted_at' => null],
        ['id' => 2, 'deleted_at' => $deleted],
    ]);
    DB::table(SD_UPG_PREFIX.'channels')->insert([
        ['id' => 1, 'deleted_at' => null],
        ['id' => 2, 'deleted_at' => $deleted],
    ]);

    upgradeMigration('*add_collection_and_channel_status.php')->up();
    upgradeMigration('*reconcile_dropped_soft_deletes.php')->up();

    // The orphaned v1 column is gone everywhere, matching a fresh v2 install.
    foreach (['products', 'product_variants', 'collections', 'channels'] as $table) {
        expect(Schema::hasColumn(SD_UPG_PREFIX.$table, 'deleted_at'))->toBeFalse();
    }

    expect(DB::table(SD_UPG_PREFIX.'products')->find(1)->status)->toBe('published')
        ->and(DB::table(SD_UPG_PREFIX.'products')->find(2)->status)->toBe('archived')
        ->and((bool) DB::table(SD_UPG_PREFIX.'product_variants')->find(1)->enabled)->toBeTrue()
        ->and((bool) DB::table(SD_UPG_PREFIX.'product_variants')->find(2)->enabled)->toBeFalse()
        // Live collections must land on 'published', NOT the 'draft' column default —
        // otherwise a merchant's whole collection tree is hidden after the upgrade.
        ->and(DB::table(SD_UPG_PREFIX.'collections')->find(1)->status)->toBe('published')
        ->and(DB::table(SD_UPG_PREFIX.'collections')->find(2)->status)->toBe('archived')
        ->and(DB::table(SD_UPG_PREFIX.'channels')->find(1)->status)->toBe('active')
        ->and(DB::table(SD_UPG_PREFIX.'channels')->find(2)->status)->toBe('inactive');
});

test('reconcile is a no-op on a fresh v2 table that never had deleted_at', function () {
    Schema::create(SD_UPG_PREFIX.'product_variants', function (Blueprint $table) {
        $table->id();
        $table->boolean('enabled')->default(true);
    });
    DB::table(SD_UPG_PREFIX.'product_variants')->insert(['id' => 1, 'enabled' => true]);

    upgradeMigration('*reconcile_dropped_soft_deletes.php')->up();

    expect((bool) DB::table(SD_UPG_PREFIX.'product_variants')->find(1)->enabled)->toBeTrue();
});
