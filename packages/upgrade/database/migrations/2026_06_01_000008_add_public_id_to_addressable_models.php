<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Lunar\Core\Database\Migration;

/**
 * v1 → v2 upgrade data step: add the `public_id` ULID external address to every
 * externally-addressable model.
 *
 * v2 gives these tables a stable, non-sequential `public_id` minted on create.
 * v1 has no such column, so for each table this adds it nullable, backfills a
 * ULID into every existing row, then tightens the column to `NOT NULL` with a
 * unique index. Each migrated ULID's timestamp is seeded from the row's
 * `created_at` (falling back to now) so backfilled ids sort chronologically,
 * exactly as freshly-minted ones do.
 *
 * The backfill runs in batches keyed on the primary key. Guarded so re-runs and
 * already-v2 databases are no-ops. There is no `down()`: upgrade-package data
 * migrations are one-way — recover from a backup if an upgrade fails rather than
 * attempting to reverse a data move.
 */
return new class extends Migration
{
    /**
     * @var list<string>
     */
    protected array $tables = [
        'products',
        'product_variants',
        'orders',
        'customers',
        'collections',
        'brands',
        'carts',
        'discounts',
    ];

    public function up(): void
    {
        foreach ($this->tables as $name) {
            $table = $this->prefix.$name;

            if (! Schema::hasTable($table)) {
                continue;
            }

            $this->addColumn($table);
            $this->backfill($table);
            $this->tighten($table);
        }
    }

    protected function addColumn(string $table): void
    {
        if (Schema::hasColumn($table, 'public_id')) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->ulid('public_id')->nullable()->after('id');
        });
    }

    protected function backfill(string $table): void
    {
        DB::table($table)
            ->whereNull('public_id')
            ->orderBy('id')
            ->each(function ($row) use ($table) {
                $time = isset($row->created_at) ? Carbon::parse($row->created_at) : Carbon::now();

                DB::table($table)
                    ->where('id', $row->id)
                    ->update(['public_id' => (string) Str::ulid($time)]);
            });
    }

    protected function tighten(string $table): void
    {
        if (! Schema::hasIndex($table, ['public_id'])) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->unique('public_id');
            });
        }

        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->ulid('public_id')->nullable(false)->change();
        });
    }
};
