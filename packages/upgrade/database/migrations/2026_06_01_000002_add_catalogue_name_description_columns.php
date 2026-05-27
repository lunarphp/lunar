<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

/**
 * v1 → v2 upgrade data step (spec 0018): promote the catalogue `name`,
 * `description` and `short_description` fields out of the `attribute_data`
 * jsonb blob into dedicated columns.
 *
 * - products / collections: `name` + `description` were system `TranslatedText`
 *   attributes; their stored `{locale: text}` map is copied into the new
 *   columns, the keys are stripped from `attribute_data`, and the backing
 *   system `Attribute` rows are deleted.
 * - brands: `name` stays a plain string column (untouched); only the new
 *   `description` / `short_description` jsonb columns are added (description
 *   backfilled from `attribute_data` when a v1 brand description attribute was
 *   present).
 *
 * Guarded so re-runs and already-v2 databases are no-ops. There is no `down()`:
 * upgrade-package data migrations are one-way — recover from a backup if an
 * upgrade fails rather than attempting to reverse a destructive data move.
 */
return new class extends Migration
{
    /**
     * @var list<string>
     */
    protected array $translatableTables = ['products', 'collections'];

    public function up(): void
    {
        foreach ($this->translatableTables as $name) {
            $table = $this->prefix.$name;

            if (! Schema::hasTable($table)) {
                continue;
            }

            $this->addColumns($table, withName: true);
            $this->backfill($table, withName: true);
        }

        $brands = $this->prefix.'brands';

        if (Schema::hasTable($brands)) {
            $this->addColumns($brands, withName: false);
            $this->backfill($brands, withName: false);
        }

        if (Schema::hasTable($this->prefix.'attributes')) {
            DB::table($this->prefix.'attributes')
                ->whereIn('attribute_type', ['product', 'collection'])
                ->whereIn('handle', ['name', 'description'])
                ->delete();
        }
    }

    protected function addColumns(string $table, bool $withName): void
    {
        $columns = $withName
            ? ['name', 'description', 'short_description']
            : ['description', 'short_description'];

        foreach ($columns as $column) {
            if (Schema::hasColumn($table, $column)) {
                continue;
            }

            Schema::table($table, function (Blueprint $table) use ($column) {
                $table->jsonb($column)->nullable();
            });
        }
    }

    protected function backfill(string $table, bool $withName): void
    {
        $handles = $withName ? ['name', 'description'] : ['description'];

        DB::table($table)->orderBy('id')->each(function ($row) use ($table, $handles) {
            $data = json_decode($row->attribute_data ?? 'null', true);

            if (! is_array($data)) {
                return;
            }

            $update = [];

            foreach ($handles as $handle) {
                if (! array_key_exists($handle, $data)) {
                    continue;
                }

                $update[$handle] = json_encode($data[$handle]['value'] ?? null);
                unset($data[$handle]);
            }

            if (! $update) {
                return;
            }

            $update['attribute_data'] = $data ? json_encode($data) : null;

            DB::table($table)->where('id', $row->id)->update($update);
        });
    }
};
