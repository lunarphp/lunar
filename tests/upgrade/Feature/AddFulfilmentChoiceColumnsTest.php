<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Tests\Upgrade\TestCase;

uses(TestCase::class)->group('cross-db');

/**
 * Isolated prefix so this stands up its own throwaway schema without touching
 * the shared lunar_* tables.
 */
const FULFILMENT_CHOICE_UPG_PREFIX = 'upgfulchoice_';

const FULFILMENT_CHOICE_UPG_TABLES = [
    'shipping_methods', 'order_lines', 'orders', 'cart_lines', 'carts', 'product_variants', 'locations', 'countries',
];

beforeEach(function () {
    config(['lunar.database.table_prefix' => FULFILMENT_CHOICE_UPG_PREFIX]);
});

afterEach(function () {
    Schema::disableForeignKeyConstraints();

    foreach (FULFILMENT_CHOICE_UPG_TABLES as $table) {
        Schema::dropIfExists(FULFILMENT_CHOICE_UPG_PREFIX.$table);
    }

    Schema::enableForeignKeyConstraints();
});

function fulfilmentChoiceUpgradeMigration(string $name): object
{
    $path = glob(dirname(__DIR__, 3)."/packages/upgrade/database/migrations/*{$name}.php");

    expect($path)->toHaveCount(1);

    return require $path[0];
}

/**
 * Stand up v1-shaped tables, plus the bare `locations` table that
 * backfill_stock_from_variants creates on a v1 database.
 */
function simulateV1FulfilmentChoiceTables(): void
{
    foreach (array_reverse(FULFILMENT_CHOICE_UPG_TABLES) as $table) {
        Schema::create(FULFILMENT_CHOICE_UPG_PREFIX.$table, function (Blueprint $blueprint) {
            $blueprint->id();
        });
    }
}

test('it adds the fulfilment choice columns', function () {
    simulateV1FulfilmentChoiceTables();

    fulfilmentChoiceUpgradeMigration('add_fulfilment_choice_columns')->up();
    fulfilmentChoiceUpgradeMigration('add_fulfilment_choice_columns')->up();

    $expected = [
        'locations' => ['line_one', 'line_two', 'line_three', 'city', 'state', 'postcode', 'country_id', 'contact_phone'],
        'carts' => ['fulfilment_method', 'pickup_location_id'],
        'cart_lines' => ['fulfilment_method'],
        'orders' => ['pickup_location_id'],
        'order_lines' => ['fulfilment_method'],
        'product_variants' => ['fulfilment_methods'],
        'shipping_methods' => ['pickup_location_id'],
    ];

    foreach ($expected as $table => $columns) {
        expect(Schema::hasColumns(FULFILMENT_CHOICE_UPG_PREFIX.$table, $columns))->toBeTrue("{$table} is missing a column");
    }

    $foreignKeys = [
        'locations' => ['country_id', 'countries'],
        'carts' => ['pickup_location_id', 'locations'],
        'orders' => ['pickup_location_id', 'locations'],
        'shipping_methods' => ['pickup_location_id', 'locations'],
    ];

    foreach ($foreignKeys as $table => [$column, $references]) {
        $constraint = collect(Schema::getForeignKeys(FULFILMENT_CHOICE_UPG_PREFIX.$table))
            ->first(fn (array $key): bool => $key['columns'] === [$column]);

        expect($constraint['foreign_table'] ?? null)->toBe(FULFILMENT_CHOICE_UPG_PREFIX.$references)
            ->and(strtolower($constraint['on_delete']))->toBe('set null');
    }
});

test('it is a no-op when the tables do not exist', function () {
    fulfilmentChoiceUpgradeMigration('add_fulfilment_choice_columns')->up();

    expect(Schema::hasTable(FULFILMENT_CHOICE_UPG_PREFIX.'carts'))->toBeFalse();
});
