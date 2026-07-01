<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\Tests\Upgrade\TestCase;

uses(TestCase::class);

/**
 * An isolated table prefix so this test stands up its own throwaway schema
 * without colliding with the real `lunar_*` tables other suites share.
 */
const SPEC0047_PREFIX = 'upg0047_';

beforeEach(function () {
    config(['lunar.database.table_prefix' => SPEC0047_PREFIX]);
});

afterEach(function () {
    Schema::dropIfExists(SPEC0047_PREFIX.'discounts');
    Schema::dropIfExists(SPEC0047_PREFIX.'promotions');
});

function promotionsMigration(): object
{
    $path = glob(dirname(__DIR__, 3).'/packages/upgrade/database/migrations/*create_promotions_and_link_discounts.php');

    return require $path[0];
}

function simulateV1Discounts(): void
{
    Schema::create(SPEC0047_PREFIX.'discounts', function (Blueprint $table) {
        $table->id();
        $table->ulid('public_id')->unique();
        $table->string('name');
        $table->timestamps();
    });

    DB::table(SPEC0047_PREFIX.'discounts')->insert([
        'id' => 1,
        'public_id' => '01JABCDEFGHIJKLMNOPQRSTUVW',
        'name' => 'Legacy discount',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

test('it creates the promotions table and links discounts', function () {
    simulateV1Discounts();

    promotionsMigration()->up();

    expect(Schema::hasTable(SPEC0047_PREFIX.'promotions'))->toBeTrue();
    expect(Schema::hasColumn(SPEC0047_PREFIX.'discounts', 'promotion_id'))->toBeTrue();

    // The existing discount survives as a standalone (null promotion).
    expect(DB::table(SPEC0047_PREFIX.'discounts')->where('id', 1)->value('promotion_id'))->toBeNull();
});

test('it is idempotent across re-runs', function () {
    simulateV1Discounts();

    $migration = promotionsMigration();
    $migration->up();
    $migration->up();

    expect(Schema::hasColumn(SPEC0047_PREFIX.'discounts', 'promotion_id'))->toBeTrue();
    expect(DB::table(SPEC0047_PREFIX.'discounts')->count())->toBe(1);
});
