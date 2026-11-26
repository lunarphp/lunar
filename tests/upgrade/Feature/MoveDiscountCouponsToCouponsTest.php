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
const DISCOUNT_COUPON_UPG_PREFIX = 'upgdiscoup_';

beforeEach(function () {
    config(['lunar.database.table_prefix' => DISCOUNT_COUPON_UPG_PREFIX]);
});

afterEach(function () {
    foreach (['coupons', 'discounts'] as $table) {
        Schema::dropIfExists(DISCOUNT_COUPON_UPG_PREFIX.$table);
    }
});

function discountCouponsMigration(): object
{
    $path = glob(dirname(__DIR__, 3).'/packages/upgrade/database/migrations/*move_discount_coupons_to_coupons.php');

    return require $path[0];
}

/**
 * v1's `discounts` table, with its single unique `coupon` column.
 */
function simulateV1DiscountCoupons(array $rows): void
{
    Schema::create(DISCOUNT_COUPON_UPG_PREFIX.'discounts', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('coupon')->nullable()->unique();
        $table->integer('uses')->unsigned()->default(0);
        $table->mediumInteger('max_uses')->unsigned()->nullable();
    });

    DB::table(DISCOUNT_COUPON_UPG_PREFIX.'discounts')->insert($rows);
}

function upgradedDiscounts(): array
{
    return DB::table(DISCOUNT_COUPON_UPG_PREFIX.'discounts')->orderBy('id')->pluck('redemption', 'name')->all();
}

function upgradedCoupons(): array
{
    return DB::table(DISCOUNT_COUPON_UPG_PREFIX.'coupons')
        ->orderBy('id')
        ->get()
        ->map(fn ($row) => [(int) $row->discount_id, $row->code, (int) $row->uses, $row->max_uses === null ? null : (int) $row->max_uses])
        ->all();
}

it('moves each discount code into a coupon and sets every discount\'s redemption', function () {
    simulateV1DiscountCoupons([
        ['id' => 1, 'name' => 'Winter', 'coupon' => 'WINTER', 'uses' => 4, 'max_uses' => 10],
        ['id' => 2, 'name' => 'Spring', 'coupon' => 'spring', 'uses' => 0, 'max_uses' => null],
        ['id' => 3, 'name' => 'Sitewide', 'coupon' => null, 'uses' => 7, 'max_uses' => null],
        ['id' => 4, 'name' => 'Blank', 'coupon' => '', 'uses' => 0, 'max_uses' => null],
    ]);

    discountCouponsMigration()->up();

    expect(Schema::hasColumn(DISCOUNT_COUPON_UPG_PREFIX.'discounts', 'coupon'))->toBeFalse()
        ->and(upgradedCoupons())->toBe([
            [1, 'WINTER', 4, 10],
            [2, 'SPRING', 0, null],
        ])
        ->and(upgradedDiscounts())->toBe([
            'Winter' => 'coupon',
            'Spring' => 'coupon',
            'Sitewide' => 'automatic',
            'Blank' => 'automatic',
        ]);
});

it('is a no-op once discounts.coupon is gone', function () {
    discountCouponsMigration()->up();

    expect(Schema::hasTable(DISCOUNT_COUPON_UPG_PREFIX.'coupons'))->toBeFalse();
});

it('converges when an earlier run stopped before dropping discounts.coupon', function () {
    simulateV1DiscountCoupons([
        ['id' => 1, 'name' => 'Winter', 'coupon' => 'WINTER', 'uses' => 4, 'max_uses' => 10],
        ['id' => 2, 'name' => 'Sitewide', 'coupon' => null, 'uses' => 0, 'max_uses' => null],
    ]);

    // The first run copied the code and set the redemption, then stopped.
    Schema::create(DISCOUNT_COUPON_UPG_PREFIX.'coupons', function (Blueprint $table) {
        $table->id();
        $table->foreignId('discount_id');
        $table->string('code')->unique();
        $table->unsignedInteger('uses')->default(0);
        $table->unsignedInteger('max_uses')->nullable();
        $table->string('email')->nullable()->index();
        $table->ulid('batch')->nullable()->index();
        $table->timestamps();
    });
    Schema::table(DISCOUNT_COUPON_UPG_PREFIX.'discounts', function (Blueprint $table) {
        $table->string('redemption')->default('automatic')->index();
    });
    DB::table(DISCOUNT_COUPON_UPG_PREFIX.'coupons')->insert(['discount_id' => 1, 'code' => 'WINTER', 'uses' => 4, 'max_uses' => 10]);

    discountCouponsMigration()->up();
    discountCouponsMigration()->up();

    expect(upgradedCoupons())->toBe([[1, 'WINTER', 4, 10]])
        ->and(upgradedDiscounts())->toBe(['Winter' => 'coupon', 'Sitewide' => 'automatic']);
});
