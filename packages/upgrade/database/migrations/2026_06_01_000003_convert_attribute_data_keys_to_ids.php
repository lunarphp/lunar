<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Lunar\Core\Database\Migration;
use Lunar\Core\Facades\DB;

/**
 * v1 → v2 upgrade data step (spec 0019): reshape attribute storage.
 *
 * 1. Rewrite every attributable table's `attribute_data` JSON from the v1
 *    handle-keyed envelope (`{handle: {field_type, value}}`) to the v2 raw
 *    id-keyed shape (`{<id>: <rawValue>}`).
 * 2. Backfill the new `attribute_models` join table from the soon-to-be-dropped
 *    `attributes.attribute_type` morph column, normalised to morph aliases.
 * 3. Backfill the new `product_type_attribute` pivot from the legacy
 *    `attributables` morph pivot (only the `product_type` rows it actually
 *    used) and drop the pivot.
 * 4. Convert `attributes.type` from FQCN (`Lunar\Core\FieldTypes\Text`) to the
 *    `FieldTypeEnum` value string (`text`).
 * 5. Flatten the v1 jsonb `name` columns on `attributes` and `attribute_groups`
 *    into the v2 plain-string column (default locale wins).
 * 6. Convert `attributes.validation_rules` from the v1 pipe-delimited string
 *    (`min:1|max:10`) to the v2 json list (`["min:1", "max:10"]`) — split on
 *    `|` exactly as Laravel's validator parses string rules.
 * 7. Add `attribute_groups.system`, drop the morph columns (and the v1
 *    indexes that still sit on them) and unused `section` / `default_value`,
 *    make `attributes.attribute_group_id` nullable.
 *
 * One-way — `down()` is intentionally absent. Restore from backup to reverse.
 */
return new class extends Migration
{
    /**
     * Tables carrying an `attribute_data` JSON column, and the morph aliases /
     * v2 FQCNs the v1 `attributes.attribute_type` column would have stored
     * for attributes owned by each table's model.
     *
     * @var array<string, list<string>>
     */
    protected array $attributableTables = [
        'products' => ['product', 'Lunar\\Core\\Models\\Product'],
        'product_variants' => ['variant', 'product_variant', 'Lunar\\Core\\Models\\ProductVariant'],
        'brands' => ['brand', 'Lunar\\Core\\Models\\Brand'],
        'collections' => ['collection', 'Lunar\\Core\\Models\\Collection'],
        'customers' => ['customer', 'Lunar\\Core\\Models\\Customer'],
        'customer_groups' => ['customer_group', 'Lunar\\Core\\Models\\CustomerGroup'],
    ];

    /**
     * v1 `FieldType` FQCN → v2 `FieldTypeEnum` value.
     *
     * @var array<string, string>
     */
    protected array $fieldTypeMap = [
        'Lunar\\Core\\FieldTypes\\Text' => 'text',
        'Lunar\\Core\\FieldTypes\\Number' => 'number',
        'Lunar\\Core\\FieldTypes\\TranslatedText' => 'translated_text',
        'Lunar\\Core\\FieldTypes\\Toggle' => 'toggle',
        'Lunar\\Core\\FieldTypes\\Dropdown' => 'dropdown',
        'Lunar\\Core\\FieldTypes\\ListField' => 'list',
        'Lunar\\Core\\FieldTypes\\File' => 'file',
        'Lunar\\Core\\FieldTypes\\Vimeo' => 'vimeo',
        'Lunar\\Core\\FieldTypes\\YouTube' => 'youtube',
    ];

    public function up(): void
    {
        $this->convertAttributeData();
        $this->backfillAttributeModels();
        $this->backfillProductTypeAttribute();
        $this->convertAttributeTypeStrings();
        $this->flattenJsonbNameColumns();
        $this->convertValidationRules();
        $this->reshapeAttributeGroupsSchema();
        $this->reshapeAttributesSchema();
    }

    protected function convertAttributeData(): void
    {
        $attributes = $this->prefix.'attributes';

        if (! Schema::hasTable($attributes) || ! Schema::hasColumn($attributes, 'attribute_type')) {
            // Either already migrated or never ran v1 — leave attribute_data alone.
            return;
        }

        foreach ($this->attributableTables as $table => $candidates) {
            $name = $this->prefix.$table;

            if (! Schema::hasTable($name) || ! Schema::hasColumn($name, 'attribute_data')) {
                continue;
            }

            $handleToId = DB::table($attributes)
                ->whereIn('attribute_type', $candidates)
                ->pluck('id', 'handle');

            if ($handleToId->isEmpty()) {
                continue;
            }

            DB::table($name)->orderBy('id')->each(function ($row) use ($name, $handleToId) {
                $data = json_decode($row->attribute_data ?? 'null', true);

                if (! is_array($data) || $data === []) {
                    return;
                }

                if ($this->isAlreadyIdKeyed($data)) {
                    return;
                }

                $converted = [];

                foreach ($data as $handle => $entry) {
                    $id = $handleToId->get($handle);

                    if ($id === null) {
                        continue;
                    }

                    $converted[(string) $id] = is_array($entry) && array_key_exists('value', $entry)
                        ? $entry['value']
                        : $entry;
                }

                DB::table($name)
                    ->where('id', $row->id)
                    ->update([
                        'attribute_data' => $converted === [] ? null : json_encode($converted),
                    ]);
            });
        }
    }

    /**
     * v2 keys are stringified positive integers; anything else (a handle) means
     * the row is still in the v1 shape.
     *
     * @param  array<int|string, mixed>  $data
     */
    protected function isAlreadyIdKeyed(array $data): bool
    {
        foreach (array_keys($data) as $key) {
            if (! ctype_digit((string) $key)) {
                return false;
            }
        }

        return true;
    }

    protected function backfillAttributeModels(): void
    {
        $attributes = $this->prefix.'attributes';
        $attributeModels = $this->prefix.'attribute_models';

        if (! Schema::hasTable($attributes) || ! Schema::hasColumn($attributes, 'attribute_type')) {
            return;
        }

        if (! Schema::hasTable($attributeModels)) {
            Schema::create($attributeModels, function (Blueprint $table) use ($attributes) {
                $table->id();
                $table->foreignId('attribute_id')->constrained($attributes)->cascadeOnDelete();
                $table->string('model_type')->index();
                $table->unique(['attribute_id', 'model_type']);
            });
        }

        DB::table($attributes)
            ->select(['id', 'attribute_type'])
            ->orderBy('id')
            ->each(function ($row) use ($attributeModels) {
                $alias = $this->normaliseMorph($row->attribute_type);

                if ($alias === null) {
                    return;
                }

                $exists = DB::table($attributeModels)
                    ->where('attribute_id', $row->id)
                    ->where('model_type', $alias)
                    ->exists();

                if ($exists) {
                    return;
                }

                DB::table($attributeModels)->insert([
                    'attribute_id' => $row->id,
                    'model_type' => $alias,
                ]);
            });
    }

    protected function backfillProductTypeAttribute(): void
    {
        $attributables = $this->prefix.'attributables';
        $pivot = $this->prefix.'product_type_attribute';
        $productTypes = $this->prefix.'product_types';
        $attributes = $this->prefix.'attributes';

        if (! Schema::hasTable($attributables)) {
            return;
        }

        if (! Schema::hasTable($pivot)) {
            Schema::create($pivot, function (Blueprint $table) use ($productTypes, $attributes) {
                $table->id();
                $table->foreignId('product_type_id')->constrained($productTypes)->cascadeOnDelete();
                $table->foreignId('attribute_id')->constrained($attributes)->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['product_type_id', 'attribute_id']);
            });
        }

        $productTypeAliases = ['product_type', 'Lunar\\Core\\Models\\ProductType'];

        DB::table($attributables)
            ->whereIn('attributable_type', $productTypeAliases)
            ->orderBy('id')
            ->each(function ($row) use ($pivot) {
                $exists = DB::table($pivot)
                    ->where('product_type_id', $row->attributable_id)
                    ->where('attribute_id', $row->attribute_id)
                    ->exists();

                if ($exists) {
                    return;
                }

                $timestamp = property_exists($row, 'created_at') ? $row->created_at : null;

                DB::table($pivot)->insert([
                    'product_type_id' => $row->attributable_id,
                    'attribute_id' => $row->attribute_id,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);
            });

        Schema::drop($attributables);
    }

    protected function convertAttributeTypeStrings(): void
    {
        $attributes = $this->prefix.'attributes';

        if (! Schema::hasTable($attributes) || ! Schema::hasColumn($attributes, 'type')) {
            return;
        }

        foreach ($this->fieldTypeMap as $fqcn => $enumValue) {
            DB::table($attributes)
                ->where('type', $fqcn)
                ->update(['type' => $enumValue]);
        }
    }

    protected function flattenJsonbNameColumns(): void
    {
        foreach (['attributes', 'attribute_groups'] as $table) {
            $name = $this->prefix.$table;

            if (! Schema::hasTable($name) || ! Schema::hasColumn($name, 'name')) {
                continue;
            }

            $flattened = DB::table($name)
                ->orderBy('id')
                ->pluck('name', 'id')
                ->map(fn ($value): ?string => $this->flattenName($value))
                ->filter(fn (?string $value): bool => $value !== null);

            // v1 stored `name` as JSON. Switch to the v2 string column before
            // writing plain values — MySQL otherwise rejects bare strings.
            Schema::table($name, function (Blueprint $blueprint) {
                $blueprint->string('name')->change();
            });

            foreach ($flattened as $id => $value) {
                DB::table($name)->where('id', $id)->update(['name' => $value]);
            }
        }
    }

    protected function flattenName(mixed $value): ?string
    {
        $decoded = is_string($value) ? json_decode($value, true) : null;

        if (! is_array($decoded)) {
            return null;
        }

        $flattened = $decoded['en'] ?? reset($decoded);

        if (! is_string($flattened)) {
            $flattened = (string) ($flattened ?? '');
        }

        return $flattened;
    }

    protected function reshapeAttributeGroupsSchema(): void
    {
        $table = $this->prefix.'attribute_groups';

        if (! Schema::hasTable($table)) {
            return;
        }

        if (! Schema::hasColumn($table, 'system')) {
            Schema::table($table, function (Blueprint $table) {
                $table->boolean('system')->default(false)->after('handle');
            });
        }

        if (Schema::hasColumn($table, 'attributable_type')) {
            // SQLite refuses DROP COLUMN while the v1 index remains.
            $this->dropIndexIfExists($table, ['attributable_type']);

            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn('attributable_type');
            });
        }
    }

    /**
     * v1 stored validation rules as a pipe-delimited string; v2 stores a json
     * list. Drop-and-recreate rather than an in-place type change so the same
     * code works on MySQL, Postgres, and SQLite. The v1 `attribute_type` morph
     * column (dropped later in this run) marks a table as unconverted, keeping
     * `up()` idempotent.
     */
    protected function convertValidationRules(): void
    {
        $table = $this->prefix.'attributes';

        if (! Schema::hasTable($table)) {
            return;
        }

        if (! Schema::hasColumn($table, 'validation_rules')) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->json('validation_rules')->nullable()->after('required');
            });

            return;
        }

        if (! Schema::hasColumn($table, 'attribute_type')) {
            return;
        }

        $rules = DB::table($table)
            ->whereNotNull('validation_rules')
            ->pluck('validation_rules', 'id')
            ->map(fn ($value) => array_values(array_filter(
                array_map('trim', explode('|', (string) $value)),
                fn (string $rule): bool => $rule !== '',
            )));

        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->dropColumn('validation_rules');
        });

        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->json('validation_rules')->nullable()->after('required');
        });

        foreach ($rules as $id => $list) {
            if ($list === []) {
                continue;
            }

            DB::table($table)->where('id', $id)->update([
                'validation_rules' => json_encode($list),
            ]);
        }
    }

    protected function reshapeAttributesSchema(): void
    {
        $table = $this->prefix.'attributes';

        if (! Schema::hasTable($table)) {
            return;
        }

        $drops = array_values(array_filter(
            ['attribute_type', 'section', 'default_value'],
            fn (string $column): bool => Schema::hasColumn($table, $column),
        ));

        if ($drops !== []) {
            if (in_array('attribute_type', $drops, true)) {
                // MariaDB 1072 / SQLite: DROP COLUMN fails while these remain.
                $this->dropIndexIfExists($table, ['attribute_type', 'handle'], 'unique');
                $this->dropIndexIfExists($table, ['attribute_type']);
            }

            Schema::table($table, function (Blueprint $blueprint) use ($drops) {
                $blueprint->dropColumn($drops);
            });
        }

        if (Schema::hasColumn($table, 'attribute_group_id')) {
            // Drop NOT NULL by re-stating the column as nullable.
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->unsignedBigInteger('attribute_group_id')->nullable()->change();
            });
        }
    }

    /**
     * Drop a v1 index by its live name. `Schema::hasIndex(..., 'index')` misses
     * SQLite, which reports the type as `btree` rather than `index`.
     *
     * @param  list<string>  $columns
     */
    protected function dropIndexIfExists(string $table, array $columns, string $type = 'index'): void
    {
        $wantUnique = $type === 'unique';

        foreach (Schema::getIndexes($table) as $index) {
            if ($index['columns'] !== $columns || (bool) $index['unique'] !== $wantUnique) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($index, $wantUnique): void {
                if ($wantUnique) {
                    $blueprint->dropUnique($index['name']);

                    return;
                }

                $blueprint->dropIndex($index['name']);
            });

            return;
        }
    }

    protected function normaliseMorph(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! str_contains($value, '\\')) {
            return $value;
        }

        return Str::snake(class_basename($value));
    }
};
