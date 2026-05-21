<?php

use Illuminate\Support\Facades\Schema;
use Lunar\Core\Base\Migration;
use Lunar\Upgrade\Rector\LunarSetList;
use Lunar\Upgrade\Support\ClassStringRewriter;

/**
 * v1 → v2 upgrade data step: rewrite persisted `Lunar\…` class strings to
 * their `Lunar\Core\…` v2 equivalents.
 *
 * Rather than enumerate which classes might appear in which columns, the
 * migration walks every column that can hold a class FQCN (polymorphic
 * `*_type` columns + the two columns that store FQCNs directly:
 * `attributes.type` for FieldType and `discounts.type` for DiscountType)
 * and runs the full `LunarSetList::V1_TO_V2_CLASS_RENAMES` map against it.
 *
 * Updates only fire where a row's existing value matches one of the v1
 * keys, so morph-aliased rows (e.g. `purchasable_type = 'product'`) are
 * left alone, and rows owned by user-land classes are untouched.
 *
 * Non-Eloquent FQCNs that v1's remap_polymorphic_relations migration
 * skipped (notably `Lunar\DataTypes\ShippingOption` persisted as the
 * `purchasable_type` of shipping order/cart lines) are picked up here.
 *
 * User-defined extension class strings are handled via
 * `config('lunar.upgrade.extensions.class_strings')`.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->rewrite(LunarSetList::V1_TO_V2_CLASS_RENAMES);
    }

    public function down(): void
    {
        $this->rewrite(array_flip(LunarSetList::V1_TO_V2_CLASS_RENAMES));
    }

    /**
     * @param  array<string, string>  $map
     */
    protected function rewrite(array $map): void
    {
        $rewriter = app(ClassStringRewriter::class);

        foreach ($this->targets() as [$table, $columns]) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ((array) $columns as $column) {
                $rewriter->rewrite($table, $column, $map);
            }
        }
    }

    /**
     * Columns that can persist a class FQCN: polymorphic morph types plus
     * the two FieldType / DiscountType columns.
     *
     * @return iterable<int, array{0: string, 1: string|array<int, string>}>
     */
    protected function targets(): iterable
    {
        $prefix = $this->prefix;

        yield [$prefix.'attributables', 'attributable_type'];
        yield [$prefix.'attribute_groups', 'attributable_type'];
        yield [$prefix.'attributes', ['attribute_type', 'type']];
        yield [$prefix.'cart_lines', 'purchasable_type'];
        yield [$prefix.'channelables', 'channelable_type'];
        yield [$prefix.'discountables', 'discountable_type'];
        yield [$prefix.'discounts', 'type'];
        yield [$prefix.'order_lines', 'purchasable_type'];
        yield [$prefix.'prices', 'priceable_type'];
        yield [$prefix.'taggables', 'taggable_type'];
        yield [$prefix.'urls', 'element_type'];

        // Shipping addon — purchasable_type on shipping exclusions.
        yield [$prefix.'shipping_exclusions', 'purchasable_type'];

        // Non-Lunar tables that may still hold core class strings.
        yield ['activity_log', ['subject_type', 'causer_type']];
        yield ['media', 'model_type'];
        yield ['model_has_permissions', 'model_type'];
        yield ['model_has_roles', 'model_type'];
    }
};
