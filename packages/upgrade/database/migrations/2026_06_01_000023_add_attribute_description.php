<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Lunar\Core\Database\Migration;
use Lunar\Core\Facades\DB;

/**
 * v1 → v2 upgrade data step (spec attribute-descriptions): bring
 * `attributes.description` to the v2 nullable string column.
 *
 * - No column (an alpha install): add it.
 * - The v1 translatable JSON column: flatten each row to one string, the
 *   `en` value when present, otherwise the first locale, truncated to 255
 *   characters, then change the column to a string.
 * - Already a string column: nothing to do.
 *
 * Guarded so re-runs and already-v2 databases are no-ops. One-way, no down().
 */
return new class extends Migration
{
    public function up(): void
    {
        $table = $this->prefix.'attributes';

        if (! Schema::hasTable($table)) {
            return;
        }

        if (! Schema::hasColumn($table, 'description')) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->string('description')->nullable()->after('name');
            });

            return;
        }

        if (in_array(Schema::getColumnType($table, 'description'), ['varchar', 'string'], true)) {
            return;
        }

        $flattened = DB::table($table)
            ->orderBy('id')
            ->pluck('description', 'id')
            ->map(fn (mixed $value): ?string => $this->flatten($value));

        // Clear the JSON values before changing the type: a stored document
        // longer than 255 characters would otherwise fail the conversion.
        DB::table($table)->update(['description' => null]);

        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->string('description')->nullable()->change();
        });

        foreach ($flattened->filter() as $id => $value) {
            DB::table($table)->where('id', $id)->update(['description' => $value]);
        }
    }

    protected function flatten(mixed $value): ?string
    {
        $decoded = is_string($value) ? json_decode($value, true) : null;

        if (! is_array($decoded) || $decoded === []) {
            return null;
        }

        $text = $decoded['en'] ?? reset($decoded);

        if (! is_string($text) || trim($text) === '') {
            return null;
        }

        return Str::limit(trim($text), 255, '');
    }
};
