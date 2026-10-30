<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\Models\Transaction;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
    Currency::factory()->create(['default' => true, 'decimal_places' => 2]);
});

function repairMigration(): object
{
    return require __DIR__.'/../../../packages/core/database/migrations/2026_10_30_000000_add_columns_missing_from_edited_create_migrations.php';
}

/** Put the tables back the way a database migrated before the create migrations were edited has them. */
function dropEditedColumns(): void
{
    Schema::table('lunar_product_options', function (Blueprint $table) {
        $table->dropIndex(['type']);
        $table->dropColumn('type');
    });

    Schema::table('lunar_attributes', fn (Blueprint $table) => $table->dropColumn('validation_rules'));
    Schema::table('lunar_order_lines', fn (Blueprint $table) => $table->dropColumn('refunded_quantity'));
}

test('adds the columns a database migrated before the edits is missing', function () {
    dropEditedColumns();

    repairMigration()->up();

    expect(Schema::hasColumn('lunar_product_options', 'type'))->toBeTrue()
        ->and(Schema::hasColumn('lunar_attributes', 'validation_rules'))->toBeTrue()
        ->and(Schema::hasColumn('lunar_order_lines', 'refunded_quantity'))->toBeTrue();
});

test('rebuilds refunded quantities from the refund lines already recorded', function () {
    $refunded = OrderLine::factory()->create(['quantity' => 5]);
    $untouched = OrderLine::factory()->create(['quantity' => 2]);
    $transaction = Transaction::factory()->create(['order_id' => $refunded->order_id, 'type' => 'refund']);

    dropEditedColumns();

    foreach ([2, 1] as $quantity) {
        DB::table('lunar_refund_lines')->insert([
            'public_id' => (string) str()->ulid(),
            'transaction_id' => $transaction->id,
            'order_line_id' => $refunded->id,
            'quantity' => $quantity,
            'amount' => 100 * $quantity,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    repairMigration()->up();

    expect(DB::table('lunar_order_lines')->where('id', $refunded->id)->value('refunded_quantity'))->toBe(3)
        ->and(DB::table('lunar_order_lines')->where('id', $untouched->id)->value('refunded_quantity'))->toBe(0);
});

test('leaves a database that already has the columns alone', function () {
    $line = OrderLine::factory()->create(['quantity' => 4]);
    DB::table('lunar_order_lines')->where('id', $line->id)->update(['refunded_quantity' => 1]);

    repairMigration()->up();

    expect(DB::table('lunar_order_lines')->where('id', $line->id)->value('refunded_quantity'))->toBe(1);
});
