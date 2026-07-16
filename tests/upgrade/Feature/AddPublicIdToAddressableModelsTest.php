<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\Tests\Upgrade\TestCase;
use Symfony\Component\Uid\Ulid;

uses(TestCase::class);

/**
 * An isolated table prefix so this test stands up its own throwaway schema
 * without colliding with the real `lunar_*` tables other suites share.
 */
const SPEC0046_PREFIX = 'upg0046_';

beforeEach(function () {
    config(['lunar.database.table_prefix' => SPEC0046_PREFIX]);
});

afterEach(function () {
    Schema::dropIfExists(SPEC0046_PREFIX.'products');
});

/**
 * Load the public_id data migration's anonymous class instance.
 */
function publicIdMigration(): object
{
    $path = glob(dirname(__DIR__, 3).'/packages/upgrade/database/migrations/*add_public_id_to_addressable_models.php');

    return require $path[0];
}

/**
 * Stand up a v1-shaped `products` table with no `public_id` column.
 */
function simulateV1ProductsWithoutPublicId(): void
{
    Schema::create(SPEC0046_PREFIX.'products', function (Blueprint $table) {
        $table->id();
        $table->string('status');
        $table->timestamps();
    });

    DB::table(SPEC0046_PREFIX.'products')->insert([
        ['id' => 1, 'status' => 'published', 'created_at' => '2024-01-01 00:00:00', 'updated_at' => now()],
        ['id' => 2, 'status' => 'published', 'created_at' => '2025-06-01 00:00:00', 'updated_at' => now()],
    ]);
}

test('it adds, backfills and tightens the public_id column', function () {
    simulateV1ProductsWithoutPublicId();

    publicIdMigration()->up();

    expect(Schema::hasColumn(SPEC0046_PREFIX.'products', 'public_id'))->toBeTrue();

    $rows = DB::table(SPEC0046_PREFIX.'products')->orderBy('id')->get();

    expect($rows->pluck('public_id')->filter())->toHaveCount(2);
    expect($rows->pluck('public_id')->unique())->toHaveCount(2);
    // ULIDs are time-ordered; the earlier-created row sorts first.
    expect($rows[0]->public_id < $rows[1]->public_id)->toBeTrue();
});

test('it seeds the ULID timestamp from created_at', function () {
    simulateV1ProductsWithoutPublicId();

    publicIdMigration()->up();

    $row = DB::table(SPEC0046_PREFIX.'products')->find(1);

    // First 10 chars of a ULID encode the millisecond timestamp; decoding them
    // back must land on the row's created_at date, not the migration run time.
    $ulid = Ulid::fromString($row->public_id);

    expect($ulid->getDateTime()->format('Y-m-d'))->toBe('2024-01-01');
});

test('it backfills every row across multiple batches', function () {
    Schema::create(SPEC0046_PREFIX.'products', function (Blueprint $table) {
        $table->id();
        $table->string('status');
        $table->timestamps();
    });

    // More rows than one batch (1000) so the backfill has to paginate while
    // its own updates shrink the whereNull result set.
    foreach (array_chunk(range(1, 1500), 500) as $ids) {
        DB::table(SPEC0046_PREFIX.'products')->insert(array_map(fn (int $id): array => [
            'id' => $id, 'status' => 'published', 'created_at' => now(), 'updated_at' => now(),
        ], $ids));
    }

    publicIdMigration()->up();

    expect(DB::table(SPEC0046_PREFIX.'products')->whereNull('public_id')->count())->toBe(0);
});

test('it is idempotent across re-runs', function () {
    simulateV1ProductsWithoutPublicId();

    $migration = publicIdMigration();
    $migration->up();

    $before = DB::table(SPEC0046_PREFIX.'products')->orderBy('id')->pluck('public_id');

    $migration->up();

    $after = DB::table(SPEC0046_PREFIX.'products')->orderBy('id')->pluck('public_id');

    expect($after->all())->toBe($before->all());
});
