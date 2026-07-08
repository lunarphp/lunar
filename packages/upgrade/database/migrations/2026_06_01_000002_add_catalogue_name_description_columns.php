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
 * - products / collections: `name` + `description` were system attributes;
 *   their stored value is copied into the new columns, the keys are stripped
 *   from `attribute_data`, and the backing system `Attribute` rows are
 *   deleted. A `TranslatedText` value already holds the `{locale: text}` map
 *   the v2 columns store; a bare `Text` scalar is keyed to the store's
 *   default language.
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
     * Memoized default language code for keying bare `Text` values.
     */
    protected string $defaultLanguage;

    /**
     * @var list<string>
     */
    protected array $translatableTables = ['products', 'collections'];

    /**
     * Every v1 attributable table whose `attribute_data` column may be written
     * NULL by this migration or by spec 0019's id-key conversion. v1 created
     * the column as `NOT NULL`; v2 treats it as nullable. Align the constraint
     * before any backfill runs.
     *
     * @var list<string>
     */
    protected array $attributableTables = [
        'products',
        'product_variants',
        'brands',
        'collections',
        'customers',
        'customer_groups',
    ];

    public function up(): void
    {
        $this->relaxAttributeDataConstraint();

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
            $orphanIds = DB::table($this->prefix.'attributes')
                ->whereIn('attribute_type', ['product', 'collection'])
                ->whereIn('handle', ['name', 'description'])
                ->pluck('id');

            if ($orphanIds->isNotEmpty() && Schema::hasTable($this->prefix.'attributables')) {
                DB::table($this->prefix.'attributables')
                    ->whereIn('attribute_id', $orphanIds)
                    ->delete();
            }

            DB::table($this->prefix.'attributes')
                ->whereIn('id', $orphanIds)
                ->delete();
        }
    }

    protected function relaxAttributeDataConstraint(): void
    {
        foreach ($this->attributableTables as $name) {
            $table = $this->prefix.$name;

            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'attribute_data')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->jsonb('attribute_data')->nullable()->change();
            });
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

                $update[$handle] = $this->translatedValue($data[$handle]['value'] ?? null);
                unset($data[$handle]);
            }

            if (! $update) {
                return;
            }

            $update['attribute_data'] = $data ? json_encode($data) : null;

            DB::table($table)->where('id', $row->id)->update($update);
        });
    }

    /**
     * Normalise an extracted attribute value to the `{locale: text}` map the
     * v2 columns store: `TranslatedText` already persists the map, while
     * `Text` persists a bare scalar, which is keyed to the default language.
     */
    protected function translatedValue(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_array($value)) {
            $value = [$this->defaultLanguage() => $value];
        }

        return json_encode($value);
    }

    protected function defaultLanguage(): string
    {
        if (isset($this->defaultLanguage)) {
            return $this->defaultLanguage;
        }

        $languages = $this->prefix.'languages';

        if (! Schema::hasTable($languages)) {
            return $this->defaultLanguage = 'en';
        }

        return $this->defaultLanguage = DB::table($languages)->where('default', true)->value('code')
            ?? DB::table($languages)->orderBy('id')->value('code')
            ?? 'en';
    }
};
