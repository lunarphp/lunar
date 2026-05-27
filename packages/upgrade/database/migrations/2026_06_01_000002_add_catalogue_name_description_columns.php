<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;
use Lunar\Core\FieldTypes\TranslatedText;

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
 * Guarded so re-runs and already-v2 databases are no-ops.
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

    public function down(): void
    {
        foreach ($this->translatableTables as $name) {
            $table = $this->prefix.$name;

            if (! Schema::hasColumn($table, 'name')) {
                continue;
            }

            $this->renest($table, withName: true);

            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn(['name', 'description', 'short_description']);
            });
        }

        $brands = $this->prefix.'brands';

        if (Schema::hasColumn($brands, 'description')) {
            $this->renest($brands, withName: false);

            Schema::table($brands, function (Blueprint $table) {
                $table->dropColumn(['description', 'short_description']);
            });
        }

        $this->recreateSystemAttributes();
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

    protected function renest(string $table, bool $withName): void
    {
        $handles = $withName ? ['name', 'description'] : ['description'];

        DB::table($table)->orderBy('id')->each(function ($row) use ($table, $handles) {
            $data = json_decode($row->attribute_data ?? 'null', true);
            $data = is_array($data) ? $data : [];

            foreach ($handles as $handle) {
                $value = json_decode($row->{$handle} ?? 'null', true);

                if ($value === null) {
                    continue;
                }

                $data[$handle] = [
                    'field_type' => TranslatedText::class,
                    'value' => $value,
                ];
            }

            DB::table($table)->where('id', $row->id)->update([
                'attribute_data' => $data ? json_encode($data) : null,
            ]);
        });
    }

    protected function recreateSystemAttributes(): void
    {
        $attributes = $this->prefix.'attributes';
        $groups = $this->prefix.'attribute_groups';

        if (! Schema::hasTable($attributes)) {
            return;
        }

        foreach (['product', 'collection'] as $type) {
            $groupId = DB::table($groups)->value('id');

            foreach ([
                ['handle' => 'name', 'position' => 1, 'required' => true, 'system' => true, 'richtext' => false],
                ['handle' => 'description', 'position' => 2, 'required' => false, 'system' => false, 'richtext' => true],
            ] as $definition) {
                $exists = DB::table($attributes)
                    ->where('attribute_type', $type)
                    ->where('handle', $definition['handle'])
                    ->exists();

                if ($exists) {
                    continue;
                }

                DB::table($attributes)->insert([
                    'attribute_type' => $type,
                    'attribute_group_id' => $groupId,
                    'position' => $definition['position'],
                    'name' => json_encode(['en' => ucfirst($definition['handle'])]),
                    'handle' => $definition['handle'],
                    'section' => 'main',
                    'type' => TranslatedText::class,
                    'required' => $definition['required'],
                    'default_value' => null,
                    'configuration' => json_encode(['richtext' => $definition['richtext']]),
                    'system' => $definition['system'],
                    'description' => json_encode(['en' => '']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
};
