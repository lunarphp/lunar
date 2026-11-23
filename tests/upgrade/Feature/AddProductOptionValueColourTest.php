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
const OPTION_COLOUR_UPG_PREFIX = 'upgoptcolour_';

beforeEach(function () {
    config(['lunar.database.table_prefix' => OPTION_COLOUR_UPG_PREFIX]);
});

afterEach(function () {
    Schema::dropIfExists(OPTION_COLOUR_UPG_PREFIX.'product_option_values');
});

function optionColourUpgradeMigration(): object
{
    $path = glob(dirname(__DIR__, 3).'/packages/upgrade/database/migrations/*add_product_option_value_colour.php');

    expect($path)->toHaveCount(1);

    return require $path[0];
}

test('it adds a nullable colour to product option values', function () {
    Schema::create(OPTION_COLOUR_UPG_PREFIX.'product_option_values', function (Blueprint $blueprint) {
        $blueprint->id();
    });

    DB::table(OPTION_COLOUR_UPG_PREFIX.'product_option_values')->insert(['id' => 1]);

    optionColourUpgradeMigration()->up();
    optionColourUpgradeMigration()->up();

    expect(DB::table(OPTION_COLOUR_UPG_PREFIX.'product_option_values')->value('colour'))->toBeNull();
});

test('it is a no-op when the table does not exist', function () {
    optionColourUpgradeMigration()->up();

    expect(Schema::hasTable(OPTION_COLOUR_UPG_PREFIX.'product_option_values'))->toBeFalse();
});
