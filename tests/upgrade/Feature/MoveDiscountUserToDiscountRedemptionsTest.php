<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\LunarServiceProvider;
use Lunar\Tests\Upgrade\TestCase;

uses(TestCase::class);

/**
 * Isolated prefix so this stands up its own throwaway schema without touching
 * the shared lunar_* tables.
 */
const DISCOUNT_REDEMPTION_UPG_PREFIX = 'upgdiscred_';

beforeEach(function () {
    // The migration builds user_id with core's userForeignKey Blueprint macro.
    $this->app->register(LunarServiceProvider::class);

    config(['lunar.database.table_prefix' => DISCOUNT_REDEMPTION_UPG_PREFIX]);
    config(['auth.providers.users.model' => DiscountRedemptionUpgradeUser::class]);
});

afterEach(function () {
    foreach (['discount_redemptions', 'discount_user', 'orders', 'carts', 'discounts', 'users'] as $table) {
        Schema::dropIfExists(DISCOUNT_REDEMPTION_UPG_PREFIX.$table);
    }
});

class DiscountRedemptionUpgradeUser extends Model
{
    protected $table = DISCOUNT_REDEMPTION_UPG_PREFIX.'users';
}

function discountRedemptionsMigration(): object
{
    $path = glob(dirname(__DIR__, 3).'/packages/upgrade/database/migrations/*move_discount_user_to_discount_redemptions.php');

    return require $path[0];
}

/**
 * Stand up the v1 tables `discount_redemptions` references, and v1's
 * `discount_user` pivot.
 */
function simulateV1DiscountUsers(array $rows): void
{
    foreach (['discounts', 'orders', 'carts', 'users'] as $table) {
        Schema::create(DISCOUNT_REDEMPTION_UPG_PREFIX.$table, function (Blueprint $table) {
            $table->id();
        });
    }

    Schema::create(DISCOUNT_REDEMPTION_UPG_PREFIX.'discount_user', function (Blueprint $table) {
        $table->id();
        $table->foreignId('discount_id');
        $table->unsignedBigInteger('user_id');
        $table->timestamps();
    });

    DB::table(DISCOUNT_REDEMPTION_UPG_PREFIX.'discounts')->insert([['id' => 1], ['id' => 2]]);
    DB::table(DISCOUNT_REDEMPTION_UPG_PREFIX.'users')->insert([['id' => 11], ['id' => 12]]);
    DB::table(DISCOUNT_REDEMPTION_UPG_PREFIX.'discount_user')->insert($rows);
}

it('copies each discount_user row into discount_redemptions and drops discount_user', function () {
    $userIds = [11, 12];

    simulateV1DiscountUsers([
        ['discount_id' => 1, 'user_id' => $userIds[0], 'created_at' => '2025-03-01 10:00:00', 'updated_at' => '2025-03-01 10:00:00'],
        ['discount_id' => 1, 'user_id' => $userIds[1], 'created_at' => '2025-04-01 10:00:00', 'updated_at' => '2025-04-01 10:00:00'],
        ['discount_id' => 2, 'user_id' => $userIds[0], 'created_at' => '2025-05-01 10:00:00', 'updated_at' => '2025-05-01 10:00:00'],
    ]);

    discountRedemptionsMigration()->up();

    $redemptions = DB::table(DISCOUNT_REDEMPTION_UPG_PREFIX.'discount_redemptions')->orderBy('id')->get();

    expect(Schema::hasTable(DISCOUNT_REDEMPTION_UPG_PREFIX.'discount_user'))->toBeFalse()
        ->and($redemptions)->toHaveCount(3)
        ->and($redemptions->map(fn ($row) => [(int) $row->discount_id, (int) $row->user_id, (string) $row->redeemed_at])->all())->toBe([
            [1, $userIds[0], '2025-03-01 10:00:00'],
            [1, $userIds[1], '2025-04-01 10:00:00'],
            [2, $userIds[0], '2025-05-01 10:00:00'],
        ])
        ->and($redemptions->pluck('order_id')->filter())->toBeEmpty()
        ->and($redemptions->pluck('cart_id')->filter())->toBeEmpty()
        ->and($redemptions->pluck('email')->filter())->toBeEmpty();
});

it('is a no-op when discount_user does not exist', function () {
    discountRedemptionsMigration()->up();

    expect(Schema::hasTable(DISCOUNT_REDEMPTION_UPG_PREFIX.'discount_redemptions'))->toBeFalse();
});

it('copies each row once when an earlier run stopped before dropping discount_user', function () {
    $rows = [
        ['discount_id' => 1, 'user_id' => 11, 'created_at' => '2025-03-01 10:00:00', 'updated_at' => '2025-03-01 10:00:00'],
        ['discount_id' => 2, 'user_id' => 12, 'created_at' => '2025-04-01 10:00:00', 'updated_at' => '2025-04-01 10:00:00'],
    ];

    simulateV1DiscountUsers($rows);
    discountRedemptionsMigration()->up();

    Schema::create(DISCOUNT_REDEMPTION_UPG_PREFIX.'discount_user', function (Blueprint $table) {
        $table->id();
        $table->foreignId('discount_id');
        $table->unsignedBigInteger('user_id');
        $table->timestamps();
    });
    DB::table(DISCOUNT_REDEMPTION_UPG_PREFIX.'discount_user')->insert($rows);

    discountRedemptionsMigration()->up();

    expect(DB::table(DISCOUNT_REDEMPTION_UPG_PREFIX.'discount_redemptions')->count())->toBe(2);
});
