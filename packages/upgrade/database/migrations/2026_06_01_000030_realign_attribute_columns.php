<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;
use Lunar\Core\Facades\DB;

/**
 * v1 → v2 upgrade data step: bring `attributes` and `attribute_groups` into
 * the shape of their v2 baselines.
 *
 * The ledger rewrite marks both baselines as run, so the columns keep their
 * v1 definitions unless this step restates them:
 *
 * - `required`, `searchable` and `system` default to false, and `position` to
 *   1, as the baselines do. v1 had no default on most of them, so a create
 *   that leaves one out fails in strict mode.
 * - `configuration` becomes nullable.
 * - `attribute_group_id` nulls itself when its group is deleted, as the
 *   baseline's foreign key does, instead of blocking the delete.
 * - `handle` is unique again. v1 made it unique per attribute type, and
 *   2026_06_01_000003 drops that index with the type column. Where two model
 *   types shared a handle, the oldest attribute keeps it and each other one
 *   gets its model type appended (`material` → `material_collection`).
 *
 * Guarded so re-runs and already-v2 databases are no-ops. One-way, no down().
 */
return new class extends Migration
{
    public function up(): void
    {
        $groups = $this->prefix.'attribute_groups';

        if (Schema::hasTable($groups)) {
            Schema::table($groups, function (Blueprint $blueprint) {
                $blueprint->integer('position')->default(1)->change();
            });
        }

        $attributes = $this->prefix.'attributes';

        if (! Schema::hasTable($attributes)) {
            return;
        }

        Schema::table($attributes, function (Blueprint $blueprint) {
            $blueprint->json('configuration')->nullable()->change();
            $blueprint->integer('position')->default(1)->change();
            $blueprint->boolean('required')->default(false)->change();
            $blueprint->boolean('searchable')->default(false)->change();
            $blueprint->boolean('system')->default(false)->change();
        });

        $this->realignGroupForeignKey($attributes, $groups);
        $this->restoreUniqueHandle($attributes);
    }

    protected function realignGroupForeignKey(string $attributes, string $groups): void
    {
        if (! $this->canDropForeignKeys() || ! Schema::hasTable($groups)) {
            return;
        }

        $foreignKey = collect(Schema::getForeignKeys($attributes))
            ->first(fn (array $key): bool => $key['columns'] === ['attribute_group_id']);

        if ($foreignKey !== null && strtolower((string) $foreignKey['on_delete']) === 'set null') {
            return;
        }

        Schema::table($attributes, function (Blueprint $blueprint) use ($foreignKey, $groups) {
            if ($foreignKey !== null) {
                $blueprint->dropForeign(['attribute_group_id']);
            }

            $blueprint->foreign('attribute_group_id')->references('id')->on($groups)->nullOnDelete();
        });
    }

    protected function restoreUniqueHandle(string $attributes): void
    {
        if (Schema::hasIndex($attributes, ['handle'], 'unique')) {
            return;
        }

        $duplicated = DB::table($attributes)
            ->select('handle')
            ->groupBy('handle')
            ->havingRaw('count(*) > 1')
            ->pluck('handle');

        $models = $this->prefix.'attribute_models';

        foreach ($duplicated as $handle) {
            $ids = DB::table($attributes)->where('handle', $handle)->orderBy('id')->pluck('id');

            foreach ($ids->slice(1) as $id) {
                $modelType = Schema::hasTable($models)
                    ? DB::table($models)->where('attribute_id', $id)->orderBy('id')->value('model_type')
                    : null;

                $renamed = $handle.'_'.($modelType ?? $id);

                if (DB::table($attributes)->where('handle', $renamed)->exists()) {
                    $renamed = $handle.'_'.$id;
                }

                DB::table($attributes)->where('id', $id)->update(['handle' => $renamed]);
            }
        }

        Schema::table($attributes, function (Blueprint $blueprint) {
            $blueprint->unique('handle');
        });
    }
};
