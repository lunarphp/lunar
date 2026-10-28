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
const ORDER_INTERNAL_NOTES_UPG_PREFIX = 'upgintnotes_';

beforeEach(function () {
    config(['lunar.database.table_prefix' => ORDER_INTERNAL_NOTES_UPG_PREFIX]);
});

afterEach(function () {
    Schema::dropIfExists(ORDER_INTERNAL_NOTES_UPG_PREFIX.'orders');
});

function orderInternalNotesMigration(): object
{
    $path = glob(dirname(__DIR__, 3).'/packages/upgrade/database/migrations/*add_order_internal_notes.php');

    return require $path[0];
}

/**
 * Stand up the v1-shaped `orders` table: a single `notes` column.
 */
function simulateV1OrderNotesRows(): void
{
    Schema::create(ORDER_INTERNAL_NOTES_UPG_PREFIX.'orders', function (Blueprint $table) {
        $table->id();
        $table->text('notes')->nullable();
        $table->timestamps();
    });

    DB::table(ORDER_INTERNAL_NOTES_UPG_PREFIX.'orders')->insert([
        ['id' => 1, 'notes' => 'Leave with the neighbour', 'created_at' => now(), 'updated_at' => now()],
        ['id' => 2, 'notes' => null, 'created_at' => now(), 'updated_at' => now()],
    ]);
}

test('it adds the internal_notes column and leaves existing notes in place', function () {
    simulateV1OrderNotesRows();

    orderInternalNotesMigration()->up();

    $table = ORDER_INTERNAL_NOTES_UPG_PREFIX.'orders';

    expect(Schema::hasColumn($table, 'internal_notes'))->toBeTrue()
        ->and(DB::table($table)->whereNotNull('internal_notes')->count())->toBe(0)
        ->and(DB::table($table)->where('id', 1)->value('notes'))->toBe('Leave with the neighbour');
});

test('it is a no-op when the internal_notes column already exists', function () {
    simulateV1OrderNotesRows();

    orderInternalNotesMigration()->up();

    $before = DB::table(ORDER_INTERNAL_NOTES_UPG_PREFIX.'orders')->orderBy('id')->get();

    orderInternalNotesMigration()->up();

    expect(DB::table(ORDER_INTERNAL_NOTES_UPG_PREFIX.'orders')->orderBy('id')->get())->toEqual($before);
});
