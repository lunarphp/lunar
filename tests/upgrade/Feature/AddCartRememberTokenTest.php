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
const CART_REMEMBER_UPG_PREFIX = 'upgcartrem_';

beforeEach(function () {
    config(['lunar.database.table_prefix' => CART_REMEMBER_UPG_PREFIX]);
});

afterEach(function () {
    Schema::dropIfExists(CART_REMEMBER_UPG_PREFIX.'carts');
});

function cartRememberTokenMigration(): object
{
    $path = glob(dirname(__DIR__, 3).'/packages/upgrade/database/migrations/*add_cart_remember_token.php');

    return require $path[0];
}

/**
 * Stand up the v1-shaped `carts` table: no `remember_token` column.
 */
function simulateV1CartRows(): void
{
    Schema::create(CART_REMEMBER_UPG_PREFIX.'carts', function (Blueprint $table) {
        $table->id();
        $table->foreignId('currency_id');
        $table->foreignId('channel_id');
        $table->timestamps();
    });

    DB::table(CART_REMEMBER_UPG_PREFIX.'carts')->insert([
        ['id' => 1, 'currency_id' => 1, 'channel_id' => 1, 'created_at' => now(), 'updated_at' => now()],
        ['id' => 2, 'currency_id' => 1, 'channel_id' => 1, 'created_at' => now(), 'updated_at' => now()],
    ]);
}

test('it adds a nullable remember_token column to carts', function () {
    simulateV1CartRows();

    cartRememberTokenMigration()->up();

    $table = CART_REMEMBER_UPG_PREFIX.'carts';

    expect(Schema::hasColumn($table, 'remember_token'))->toBeTrue()
        ->and(DB::table($table)->whereNull('remember_token')->count())->toBe(2);
});

test('it is a no-op when the remember_token column already exists', function () {
    simulateV1CartRows();

    cartRememberTokenMigration()->up();

    $before = DB::table(CART_REMEMBER_UPG_PREFIX.'carts')->orderBy('id')->get();

    cartRememberTokenMigration()->up();

    expect(DB::table(CART_REMEMBER_UPG_PREFIX.'carts')->orderBy('id')->get())->toEqual($before);
});

test('it is a no-op when the carts table does not exist', function () {
    cartRememberTokenMigration()->up();

    expect(Schema::hasTable(CART_REMEMBER_UPG_PREFIX.'carts'))->toBeFalse();
});
