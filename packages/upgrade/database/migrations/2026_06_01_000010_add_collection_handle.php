<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Lunar\Core\Database\Migration;

/**
 * v1 → v2 upgrade data step (spec 0055): add `collections.handle`.
 *
 * v2 gives collections a unique kebab-case handle (the stable programmatic
 * key, matching brands). v1 had no such column: handles are backfilled from
 * the collection's default-language name, suffixed until unique
 * (`collection`, `collection-2`, ...). Runs after the catalogue name
 * promotion (step 0002), so `name` is already the `{locale: text}` map.
 *
 * Guarded so re-runs and already-v2 databases are no-ops. There is no `down()`:
 * upgrade-package data migrations are one-way — recover from a backup if an
 * upgrade fails rather than attempting to reverse a data move.
 */
return new class extends Migration
{
    public function up(): void
    {
        $table = $this->prefix.'collections';

        if (! Schema::hasTable($table) || Schema::hasColumn($table, 'handle')) {
            return;
        }

        // Added nullable, backfilled, then constrained — the unique index
        // cannot be created while every row holds NULL on some drivers.
        Schema::table($table, function ($blueprint) {
            $blueprint->string('handle')->nullable();
        });

        $defaultLanguage = $this->defaultLanguageCode();
        $taken = [];

        DB::table($table)->orderBy('id')->each(function ($collection) use ($table, $defaultLanguage, &$taken) {
            $names = json_decode($collection->name ?? '', true) ?: [];
            $name = $names[$defaultLanguage] ?? (reset($names) ?: '');

            $base = Str::slug($name) ?: 'collection';
            $handle = $base;

            for ($suffix = 2; isset($taken[$handle]); $suffix++) {
                $handle = $base.'-'.$suffix;
            }

            $taken[$handle] = true;

            DB::table($table)->where('id', $collection->id)->update([
                'handle' => $handle,
            ]);
        });

        Schema::table($table, function ($blueprint) {
            $blueprint->string('handle')->nullable(false)->change();
            $blueprint->unique('handle');
        });
    }

    protected function defaultLanguageCode(): string
    {
        $languages = $this->prefix.'languages';

        if (! Schema::hasTable($languages)) {
            return 'en';
        }

        return DB::table($languages)->where('default', true)->value('code')
            ?? DB::table($languages)->orderBy('id')->value('code')
            ?? 'en';
    }
};
