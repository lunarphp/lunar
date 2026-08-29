<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\Tests\Upgrade\TestCase;
use Lunar\Upgrade\Steps\DataMigrationStep;

uses(TestCase::class);

/**
 * Isolated prefix so this stands up its own throwaway schema without touching
 * the shared lunar_* tables.
 */
const DISCOUNT_UPG_PREFIX = 'upgdisc_';

beforeEach(function () {
    config(['lunar.database.table_prefix' => DISCOUNT_UPG_PREFIX]);
});

afterEach(function () {
    Schema::dropIfExists(DISCOUNT_UPG_PREFIX.'discounts');
});

function splitAmountOffMigration(): object
{
    $path = glob(dirname(__DIR__, 3).'/packages/upgrade/database/migrations/*split_amount_off_discount_type.php');

    return require $path[0];
}

/**
 * Stand up the mid-upgrade `discounts` table. The class-string rewrite (step
 * 0000) has already run, so stored types carry their `Lunar\Core` names.
 */
function simulateDiscountRows(array $rows): void
{
    Schema::create(DISCOUNT_UPG_PREFIX.'discounts', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('type');
        $table->text('data')->nullable();
        $table->timestamps();
    });

    DB::table(DISCOUNT_UPG_PREFIX.'discounts')->insert($rows);
}

function discountRow(int $id): object
{
    return DB::table(DISCOUNT_UPG_PREFIX.'discounts')->where('id', $id)->first();
}

it('converts a fixed-value discount to FixedAmountOff and renames its amounts', function () {
    simulateDiscountRows([[
        'id' => 1,
        'name' => 'Twenty off',
        'type' => 'Lunar\\Core\\DiscountTypes\\AmountOff',
        'data' => json_encode([
            'fixed_value' => true,
            'fixed_values' => ['GBP' => 2000, 'USD' => 2500],
            'min_prices' => ['GBP' => 5000],
        ]),
        'created_at' => now(),
        'updated_at' => now(),
    ]]);

    splitAmountOffMigration()->up();

    $discount = discountRow(1);
    $data = json_decode($discount->data, true);

    expect($discount->type)->toBe('Lunar\\Core\\DiscountTypes\\FixedAmountOff');
    expect($data)->toEqual([
        'amounts' => ['GBP' => 2000, 'USD' => 2500],
        'min_prices' => ['GBP' => 5000],
    ]);
});

it('converts a percentage discount to PercentageOff', function () {
    simulateDiscountRows([[
        'id' => 1,
        'name' => 'Ten percent',
        'type' => 'Lunar\\Core\\DiscountTypes\\AmountOff',
        'data' => json_encode(['fixed_value' => false, 'percentage' => 10]),
        'created_at' => now(),
        'updated_at' => now(),
    ]]);

    splitAmountOffMigration()->up();

    $discount = discountRow(1);

    expect($discount->type)->toBe('Lunar\\Core\\DiscountTypes\\PercentageOff');
    expect(json_decode($discount->data, true))->toBe(['percentage' => 10]);
});

it('treats a missing fixed_value flag as a percentage discount', function () {
    // AmountOff read the flag with a `?? false` default, so an absent flag ran
    // the percentage path — the conversion has to agree.
    simulateDiscountRows([[
        'id' => 1,
        'name' => 'Malformed',
        'type' => 'Lunar\\Core\\DiscountTypes\\AmountOff',
        'data' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]]);

    splitAmountOffMigration()->up();

    expect(discountRow(1)->type)->toBe('Lunar\\Core\\DiscountTypes\\PercentageOff');
});

it('converts rows still carrying the v1 class name', function () {
    simulateDiscountRows([[
        'id' => 1,
        'name' => 'Legacy',
        'type' => 'Lunar\\DiscountTypes\\AmountOff',
        'data' => json_encode(['fixed_value' => true, 'fixed_values' => ['GBP' => 500]]),
        'created_at' => now(),
        'updated_at' => now(),
    ]]);

    splitAmountOffMigration()->up();

    expect(discountRow(1)->type)->toBe('Lunar\\Core\\DiscountTypes\\FixedAmountOff');
});

it('leaves other discount types alone', function () {
    simulateDiscountRows([[
        'id' => 1,
        'name' => 'Buy two get one',
        'type' => 'Lunar\\Core\\DiscountTypes\\BuyXGetY',
        'data' => json_encode(['min_qty' => 2, 'reward_qty' => 1]),
        'created_at' => now(),
        'updated_at' => now(),
    ]]);

    splitAmountOffMigration()->up();

    $discount = discountRow(1);

    expect($discount->type)->toBe('Lunar\\Core\\DiscountTypes\\BuyXGetY');
    expect(json_decode($discount->data, true))->toBe(['min_qty' => 2, 'reward_qty' => 1]);
});

it('is idempotent', function () {
    simulateDiscountRows([[
        'id' => 1,
        'name' => 'Twenty off',
        'type' => 'Lunar\\Core\\DiscountTypes\\AmountOff',
        'data' => json_encode(['fixed_value' => true, 'fixed_values' => ['GBP' => 2000]]),
        'created_at' => now(),
        'updated_at' => now(),
    ]]);

    $migration = splitAmountOffMigration();
    $migration->up();
    $first = discountRow(1);

    $migration->up();

    expect(discountRow(1))->toEqual($first);
});

it('converts a mixed table in one pass', function () {
    simulateDiscountRows([
        [
            'id' => 1,
            'name' => 'Percentage',
            'type' => 'Lunar\\Core\\DiscountTypes\\AmountOff',
            'data' => json_encode(['fixed_value' => false, 'percentage' => 15]),
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => 2,
            'name' => 'Fixed',
            'type' => 'Lunar\\Core\\DiscountTypes\\AmountOff',
            'data' => json_encode(['fixed_value' => true, 'fixed_values' => ['GBP' => 1000]]),
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => 3,
            'name' => 'Untouched',
            'type' => 'Lunar\\Core\\DiscountTypes\\BuyXGetY',
            'data' => json_encode(['min_qty' => 3]),
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    splitAmountOffMigration()->up();

    expect(discountRow(1)->type)->toBe('Lunar\\Core\\DiscountTypes\\PercentageOff');
    expect(discountRow(2)->type)->toBe('Lunar\\Core\\DiscountTypes\\FixedAmountOff');
    expect(discountRow(3)->type)->toBe('Lunar\\Core\\DiscountTypes\\BuyXGetY');
});

it('does nothing when the discounts table is absent', function () {
    splitAmountOffMigration()->up();
})->throwsNoExceptions();

it('reports the split as a manual action, since no Rector rule can decide it', function () {
    expect(DataMigrationStep::MANUAL_ACTIONS)->toHaveCount(1);
    expect(DataMigrationStep::MANUAL_ACTIONS[0])
        ->toContain('AmountOff')
        ->toContain('PercentageOff')
        ->toContain('FixedAmountOff')
        ->toContain('data.amounts');
});
