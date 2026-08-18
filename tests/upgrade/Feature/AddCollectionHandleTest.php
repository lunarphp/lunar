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
const COLLECTION_UPG_PREFIX = 'upgcoll_';

beforeEach(function () {
    config(['lunar.database.table_prefix' => COLLECTION_UPG_PREFIX]);
});

afterEach(function () {
    Schema::dropIfExists(COLLECTION_UPG_PREFIX.'collections');
    Schema::dropIfExists(COLLECTION_UPG_PREFIX.'languages');
});

function collectionHandleMigration(): object
{
    $path = glob(dirname(__DIR__, 3).'/packages/upgrade/database/migrations/*add_collection_handle.php');

    return require $path[0];
}

/**
 * Stand up the mid-upgrade `collections` table: the catalogue name promotion
 * (step 0002) has already run, so `name` holds the `{locale: text}` map, but
 * no `handle` exists yet.
 */
function simulateV1CollectionRows(): void
{
    Schema::create(COLLECTION_UPG_PREFIX.'languages', function (Blueprint $table) {
        $table->id();
        $table->string('code');
        $table->boolean('default')->default(false);
        $table->timestamps();
    });

    DB::table(COLLECTION_UPG_PREFIX.'languages')->insert([
        ['id' => 1, 'code' => 'fr', 'default' => false, 'created_at' => now(), 'updated_at' => now()],
        ['id' => 2, 'code' => 'en', 'default' => true, 'created_at' => now(), 'updated_at' => now()],
    ]);

    Schema::create(COLLECTION_UPG_PREFIX.'collections', function (Blueprint $table) {
        $table->id();
        $table->jsonb('name')->nullable();
        $table->timestamps();
    });

    DB::table(COLLECTION_UPG_PREFIX.'collections')->insert([
        ['id' => 1, 'name' => json_encode(['en' => 'Summer Sale', 'fr' => 'Soldes']), 'created_at' => now(), 'updated_at' => now()],
        ['id' => 2, 'name' => json_encode(['en' => 'Summer  Sale']), 'created_at' => now(), 'updated_at' => now()],
        ['id' => 3, 'name' => json_encode(['fr' => 'Hiver']), 'created_at' => now(), 'updated_at' => now()],
        ['id' => 4, 'name' => null, 'created_at' => now(), 'updated_at' => now()],
    ]);
}

test('it adds the column and backfills unique handles from the default-language name', function () {
    simulateV1CollectionRows();

    collectionHandleMigration()->up();

    $table = COLLECTION_UPG_PREFIX.'collections';

    expect(Schema::hasColumn($table, 'handle'))->toBeTrue();

    $rows = DB::table($table)->orderBy('id')->get();

    // Both Summer Sale rows slug identically; the second gets a suffix. The
    // fr-only row falls back to its first translation, the nameless row to
    // the generic base.
    expect($rows[0]->handle)->toBe('summer-sale')
        ->and($rows[1]->handle)->toBe('summer-sale-2')
        ->and($rows[2]->handle)->toBe('hiver')
        ->and($rows[3]->handle)->toBe('collection');
});

test('it is a no-op when the handle column already exists', function () {
    simulateV1CollectionRows();

    collectionHandleMigration()->up();

    $before = DB::table(COLLECTION_UPG_PREFIX.'collections')->orderBy('id')->get();

    collectionHandleMigration()->up();

    expect(DB::table(COLLECTION_UPG_PREFIX.'collections')->orderBy('id')->get())->toEqual($before);
});
