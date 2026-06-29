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
const REGION_UPG_PREFIX = 'upgregion_';

beforeEach(function () {
    config(['lunar.database.table_prefix' => REGION_UPG_PREFIX]);
});

afterEach(function () {
    foreach (['country_region', 'regions', 'carts', 'orders', 'tax_zones', 'languages', 'currencies', 'channels', 'countries'] as $table) {
        Schema::dropIfExists(REGION_UPG_PREFIX.$table);
    }
});

function regionMigration(): object
{
    $path = glob(dirname(__DIR__, 3).'/packages/upgrade/database/migrations/*seed_default_region_and_backfill.php');

    return require $path[0];
}

/**
 * Stand up the v1-shaped tables: the foundation tables a region seeds from, plus
 * carts and orders carrying no `region_id`, and no region tables at all.
 */
function simulateV1Foundation(): void
{
    Schema::create(REGION_UPG_PREFIX.'channels', function (Blueprint $table) {
        $table->id();
        $table->boolean('default')->default(false);
    });
    Schema::create(REGION_UPG_PREFIX.'currencies', function (Blueprint $table) {
        $table->id();
        $table->boolean('default')->default(false);
    });
    Schema::create(REGION_UPG_PREFIX.'languages', function (Blueprint $table) {
        $table->id();
        $table->boolean('default')->default(false);
    });
    Schema::create(REGION_UPG_PREFIX.'tax_zones', function (Blueprint $table) {
        $table->id();
        $table->boolean('default')->default(false);
    });
    Schema::create(REGION_UPG_PREFIX.'countries', function (Blueprint $table) {
        $table->id();
    });
    Schema::create(REGION_UPG_PREFIX.'carts', function (Blueprint $table) {
        $table->id();
    });
    Schema::create(REGION_UPG_PREFIX.'orders', function (Blueprint $table) {
        $table->id();
    });

    DB::table(REGION_UPG_PREFIX.'channels')->insert([['id' => 1, 'default' => false], ['id' => 2, 'default' => true]]);
    DB::table(REGION_UPG_PREFIX.'currencies')->insert([['id' => 1, 'default' => true]]);
    DB::table(REGION_UPG_PREFIX.'languages')->insert([['id' => 1, 'default' => true]]);
    DB::table(REGION_UPG_PREFIX.'tax_zones')->insert([['id' => 1, 'default' => true]]);
    DB::table(REGION_UPG_PREFIX.'carts')->insert([['id' => 1], ['id' => 2]]);
    DB::table(REGION_UPG_PREFIX.'orders')->insert([['id' => 1]]);
}

test('it seeds a catch-all default region and backfills carts and orders', function () {
    simulateV1Foundation();

    regionMigration()->up();

    $region = DB::table(REGION_UPG_PREFIX.'regions')->where('default', true)->first();

    expect($region)->not->toBeNull()
        ->and($region->handle)->toBe('default')
        ->and((int) $region->channel_id)->toBe(2)       // the default channel, not the lowest id
        ->and((int) $region->currency_id)->toBe(1)
        ->and((int) $region->language_id)->toBe(1)
        ->and((int) $region->tax_zone_id)->toBe(1)
        ->and($region->prices_inc_tax)->toBeNull();

    // Catch-all: no countries attached.
    expect(DB::table(REGION_UPG_PREFIX.'country_region')->count())->toBe(0);

    // Every cart and order now points at the default region.
    expect(DB::table(REGION_UPG_PREFIX.'carts')->where('region_id', $region->id)->count())->toBe(2)
        ->and(DB::table(REGION_UPG_PREFIX.'orders')->where('region_id', $region->id)->count())->toBe(1);
});

test('it is a no-op when a default region already exists', function () {
    simulateV1Foundation();

    regionMigration()->up();
    $first = DB::table(REGION_UPG_PREFIX.'regions')->where('default', true)->value('id');

    // A second pass must not seed another region or move the stamped ids.
    regionMigration()->up();

    expect(DB::table(REGION_UPG_PREFIX.'regions')->where('default', true)->count())->toBe(1)
        ->and(DB::table(REGION_UPG_PREFIX.'regions')->where('default', true)->value('id'))->toBe($first);
});

test('it leaves a cart that already carries a region untouched', function () {
    simulateV1Foundation();

    // Simulate a partial upgrade: the region table and the carts.region_id column
    // already exist, with a non-default region stamped on one cart.
    Schema::create(REGION_UPG_PREFIX.'regions', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('handle')->unique();
        $table->unsignedBigInteger('channel_id');
        $table->unsignedBigInteger('currency_id');
        $table->unsignedBigInteger('language_id');
        $table->unsignedBigInteger('tax_zone_id')->nullable();
        $table->boolean('prices_inc_tax')->nullable();
        $table->boolean('default')->default(false);
        $table->timestamps();
    });
    Schema::table(REGION_UPG_PREFIX.'carts', function (Blueprint $table) {
        $table->unsignedBigInteger('region_id')->nullable();
    });

    $explicit = DB::table(REGION_UPG_PREFIX.'regions')->insertGetId([
        'name' => 'EU', 'handle' => 'eu', 'channel_id' => 2, 'currency_id' => 1,
        'language_id' => 1, 'tax_zone_id' => 1, 'default' => false,
        'created_at' => now(), 'updated_at' => now(),
    ]);
    DB::table(REGION_UPG_PREFIX.'carts')->where('id', 1)->update(['region_id' => $explicit]);

    regionMigration()->up();

    $default = DB::table(REGION_UPG_PREFIX.'regions')->where('default', true)->value('id');

    expect((int) DB::table(REGION_UPG_PREFIX.'carts')->find(1)->region_id)->toBe($explicit)
        ->and((int) DB::table(REGION_UPG_PREFIX.'carts')->find(2)->region_id)->toBe((int) $default);
});
