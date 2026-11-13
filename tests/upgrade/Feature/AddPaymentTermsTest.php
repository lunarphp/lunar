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
const PAYMENT_TERMS_UPG_PREFIX = 'upgpayterms_';

beforeEach(function () {
    config(['lunar.database.table_prefix' => PAYMENT_TERMS_UPG_PREFIX]);
});

afterEach(function () {
    Schema::dropIfExists(PAYMENT_TERMS_UPG_PREFIX.'carts');
    Schema::dropIfExists(PAYMENT_TERMS_UPG_PREFIX.'orders');
});

function paymentTermsUpgradeMigration(): object
{
    $path = glob(dirname(__DIR__, 3).'/packages/upgrade/database/migrations/*add_payment_terms.php');

    expect($path)->toHaveCount(1);

    return require $path[0];
}

test('it adds payment terms to carts and orders, defaulting orders to checkout', function () {
    foreach (['carts', 'orders'] as $table) {
        Schema::create(PAYMENT_TERMS_UPG_PREFIX.$table, function (Blueprint $blueprint) {
            $blueprint->id();
        });

        DB::table(PAYMENT_TERMS_UPG_PREFIX.$table)->insert(['id' => 1]);
    }

    paymentTermsUpgradeMigration()->up();
    paymentTermsUpgradeMigration()->up();

    expect(DB::table(PAYMENT_TERMS_UPG_PREFIX.'carts')->value('payment_terms'))->toBeNull()
        ->and(DB::table(PAYMENT_TERMS_UPG_PREFIX.'orders')->value('payment_terms'))->toBe('checkout');
});

test('it is a no-op when the tables do not exist', function () {
    paymentTermsUpgradeMigration()->up();

    expect(Schema::hasTable(PAYMENT_TERMS_UPG_PREFIX.'carts'))->toBeFalse();
});
