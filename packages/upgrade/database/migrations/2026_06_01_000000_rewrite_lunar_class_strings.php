<?php

use Illuminate\Support\Facades\Schema;
use Lunar\Core\Base\Migration;
use Lunar\Upgrade\Rector\LunarSetList;
use Lunar\Upgrade\Support\ClassStringRewriter;

/**
 * v1 → v2 upgrade data step: rewrite persisted `Lunar\…` class strings to
 * their `Lunar\Core\…` v2 equivalents.
 *
 * Only the columns that store FQCNs (not morph aliases) need updating:
 *   - `attributes.type`   — field type class
 *   - `discounts.type`    — discount type class
 *
 * Polymorphic morph columns (`*_type`) already store aliases like 'product'
 * by the time a v1.x install is on its latest release — v1's
 * remap_polymorphic_relations migration converted them. The Rector ruleset
 * shipped under `LunarSetList::V1_TO_V2_CLASS_RENAMES` covers user code.
 * User-defined class strings persisted by extensions are handled via
 * `config('lunar.upgrade.extensions.class_strings')`.
 */
return new class extends Migration
{
    public function up(): void
    {
        $rewriter = app(ClassStringRewriter::class);

        foreach ($this->rewrites() as [$table, $column, $map]) {
            if ($map === [] || ! Schema::hasTable($this->prefix.$table)) {
                continue;
            }

            $rewriter->rewrite($this->prefix.$table, $column, $map);
        }
    }

    public function down(): void
    {
        $rewriter = app(ClassStringRewriter::class);

        foreach ($this->rewrites() as [$table, $column, $map]) {
            if ($map === [] || ! Schema::hasTable($this->prefix.$table)) {
                continue;
            }

            $rewriter->rewrite($this->prefix.$table, $column, array_flip($map));
        }
    }

    /**
     * @return array<int, array{0: string, 1: string, 2: array<string, string>}>
     */
    protected function rewrites(): array
    {
        return [
            ['attributes', 'type', $this->filterByPrefix('Lunar\\FieldTypes\\')],
            ['discounts', 'type', $this->filterByPrefix('Lunar\\DiscountTypes\\')],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function filterByPrefix(string $prefix): array
    {
        return array_filter(
            LunarSetList::V1_TO_V2_CLASS_RENAMES,
            fn (string $from): bool => str_starts_with($from, $prefix),
            ARRAY_FILTER_USE_KEY,
        );
    }
};
