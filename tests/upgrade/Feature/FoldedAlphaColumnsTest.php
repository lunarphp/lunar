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
const FOLDED_ALPHA_UPG_PREFIX = 'upgfolded_';

beforeEach(function () {
    config(['lunar.database.table_prefix' => FOLDED_ALPHA_UPG_PREFIX]);
});

afterEach(function () {
    Schema::dropIfExists(FOLDED_ALPHA_UPG_PREFIX.'tax_zones');
    Schema::dropIfExists(FOLDED_ALPHA_UPG_PREFIX.'staff');
});

function foldedAlphaMigration(string $name): object
{
    $path = glob(dirname(__DIR__, 3)."/packages/upgrade/database/migrations/*{$name}.php");

    return require $path[0];
}

test('it drops price_display from a v1 tax_zones table', function () {
    Schema::create(FOLDED_ALPHA_UPG_PREFIX.'tax_zones', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('price_display');
    });

    DB::table(FOLDED_ALPHA_UPG_PREFIX.'tax_zones')->insert(['name' => 'UK', 'price_display' => 'tax_inclusive']);

    foldedAlphaMigration('drop_tax_zone_price_display')->up();
    foldedAlphaMigration('drop_tax_zone_price_display')->up();

    expect(Schema::hasColumn(FOLDED_ALPHA_UPG_PREFIX.'tax_zones', 'price_display'))->toBeFalse()
        ->and(DB::table(FOLDED_ALPHA_UPG_PREFIX.'tax_zones')->value('name'))->toBe('UK');
});

test('it adds a nullable preferred_locale to a v1 staff table', function () {
    Schema::create(FOLDED_ALPHA_UPG_PREFIX.'staff', function (Blueprint $table) {
        $table->id();
        $table->string('email');
    });

    DB::table(FOLDED_ALPHA_UPG_PREFIX.'staff')->insert(['email' => 'staff@example.com']);

    foldedAlphaMigration('add_staff_preferred_locale')->up();
    foldedAlphaMigration('add_staff_preferred_locale')->up();

    expect(Schema::hasColumn(FOLDED_ALPHA_UPG_PREFIX.'staff', 'preferred_locale'))->toBeTrue()
        ->and(DB::table(FOLDED_ALPHA_UPG_PREFIX.'staff')->value('preferred_locale'))->toBeNull();
});

test('both migrations are no-ops when their tables do not exist', function () {
    foldedAlphaMigration('drop_tax_zone_price_display')->up();
    foldedAlphaMigration('add_staff_preferred_locale')->up();

    expect(Schema::hasTable(FOLDED_ALPHA_UPG_PREFIX.'tax_zones'))->toBeFalse()
        ->and(Schema::hasTable(FOLDED_ALPHA_UPG_PREFIX.'staff'))->toBeFalse();
});
