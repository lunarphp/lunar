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
const FULFIL_UPG_PREFIX = 'upgfulfil_';

beforeEach(function () {
    config(['lunar.database.table_prefix' => FULFIL_UPG_PREFIX]);
});

afterEach(function () {
    Schema::dropIfExists(FULFIL_UPG_PREFIX.'order_lines');
});

function requiresFulfilmentMigration(): object
{
    $path = glob(dirname(__DIR__, 3).'/packages/upgrade/database/migrations/*backfill_order_line_requires_fulfilment.php');

    return require $path[0];
}

/**
 * Stand up the v1-shaped `order_lines` table: no `requires_shipping` and no
 * `requires_fulfilment` — v1 had neither column.
 */
function simulateV1OrderLineRows(): void
{
    Schema::create(FULFIL_UPG_PREFIX.'order_lines', function (Blueprint $table) {
        $table->id();
        $table->string('type');
        $table->string('description');
        $table->timestamps();
    });

    DB::table(FULFIL_UPG_PREFIX.'order_lines')->insert([
        ['id' => 1, 'type' => 'physical', 'description' => 'Blue Tee', 'created_at' => now(), 'updated_at' => now()],
        ['id' => 2, 'type' => 'digital', 'description' => 'Gift Card', 'created_at' => now(), 'updated_at' => now()],
        ['id' => 3, 'type' => 'shipping', 'description' => 'Standard Delivery', 'created_at' => now(), 'updated_at' => now()],
    ]);
}

test('it adds the columns and marks physical lines', function () {
    simulateV1OrderLineRows();

    requiresFulfilmentMigration()->up();

    $table = FULFIL_UPG_PREFIX.'order_lines';

    expect(Schema::hasColumn($table, 'requires_shipping'))->toBeTrue()
        ->and(Schema::hasColumn($table, 'requires_fulfilment'))->toBeTrue();

    $physical = DB::table($table)->find(1);
    expect((bool) $physical->requires_shipping)->toBeTrue()
        ->and((bool) $physical->requires_fulfilment)->toBeTrue();

    // Non-physical lines keep the false default.
    foreach ([2, 3] as $id) {
        $line = DB::table($table)->find($id);
        expect((bool) $line->requires_shipping)->toBeFalse()
            ->and((bool) $line->requires_fulfilment)->toBeFalse();
    }
});

test('it is a no-op on an already-migrated database', function () {
    Schema::create(FULFIL_UPG_PREFIX.'order_lines', function (Blueprint $table) {
        $table->id();
        $table->string('type');
        $table->boolean('requires_shipping')->default(false);
        $table->boolean('requires_fulfilment')->default(false);
        $table->timestamps();
    });

    DB::table(FULFIL_UPG_PREFIX.'order_lines')->insert([
        'id' => 1, 'type' => 'physical', 'requires_shipping' => true, 'requires_fulfilment' => true, 'created_at' => now(), 'updated_at' => now(),
    ]);

    requiresFulfilmentMigration()->up();

    $line = DB::table(FULFIL_UPG_PREFIX.'order_lines')->find(1);
    expect((bool) $line->requires_fulfilment)->toBeTrue();
});

test('it leaves deliberate per-line choices alone when the columns pre-exist', function () {
    // v2-shaped table: a physical line a store deliberately marked as not
    // fulfillable must not be flipped by a re-run.
    Schema::create(FULFIL_UPG_PREFIX.'order_lines', function (Blueprint $table) {
        $table->id();
        $table->string('type');
        $table->boolean('requires_shipping')->default(false);
        $table->boolean('requires_fulfilment')->default(false);
        $table->timestamps();
    });

    DB::table(FULFIL_UPG_PREFIX.'order_lines')->insert([
        'id' => 1, 'type' => 'physical', 'requires_shipping' => false, 'requires_fulfilment' => false, 'created_at' => now(), 'updated_at' => now(),
    ]);

    requiresFulfilmentMigration()->up();

    $line = DB::table(FULFIL_UPG_PREFIX.'order_lines')->find(1);
    expect((bool) $line->requires_fulfilment)->toBeFalse()
        ->and((bool) $line->requires_shipping)->toBeFalse();
});
