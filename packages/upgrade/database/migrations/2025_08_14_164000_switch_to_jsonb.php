<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Base\Migration;

/**
 * Postgres-only v1 → v2 upgrade step: convert legacy json columns to jsonb.
 *
 * Fresh v2 installs declare these columns as jsonb in their create migrations,
 * so this runs only for users upgrading from a v1.x install whose schema was
 * created with the original json() Blueprint calls.
 */
return new class extends Migration
{
    private array $columnsToUpdate = [
        'activity_log' => [
            ['name' => 'properties', 'nullable' => true],
        ],
        'addresses' => [
            ['name' => 'meta', 'nullable' => true],
        ],
        'attribute_groups' => [
            ['name' => 'name', 'nullable' => false],
        ],
        'attributes' => [
            ['name' => 'configuration', 'nullable' => false],
            ['name' => 'description', 'nullable' => true],
            ['name' => 'name', 'nullable' => false],
        ],
        'brands' => [
            ['name' => 'attribute_data', 'nullable' => true],
        ],
        'cart_addresses' => [
            ['name' => 'meta', 'nullable' => true],
        ],
        'cart_lines' => [
            ['name' => 'meta', 'nullable' => true],
        ],
        'carts' => [
            ['name' => 'meta', 'nullable' => true],
        ],
        'collections' => [
            ['name' => 'attribute_data', 'nullable' => true],
        ],
        'customer_groups' => [
            ['name' => 'attribute_data', 'nullable' => true],
        ],
        'customers' => [
            ['name' => 'attribute_data', 'nullable' => true],
            ['name' => 'meta', 'nullable' => true],
        ],
        'discounts' => [
            ['name' => 'data', 'nullable' => true],
        ],
        'order_addresses' => [
            ['name' => 'meta', 'nullable' => true],
        ],
        'order_lines' => [
            ['name' => 'meta', 'nullable' => true],
            ['name' => 'tax_breakdown', 'nullable' => false],
        ],
        'orders' => [
            ['name' => 'discount_breakdown', 'nullable' => true],
            ['name' => 'meta', 'nullable' => true],
            ['name' => 'shipping_breakdown', 'nullable' => true],
            ['name' => 'tax_breakdown', 'nullable' => false],
        ],
        'product_option_values' => [
            ['name' => 'name', 'nullable' => false],
        ],
        'product_options' => [
            ['name' => 'label', 'nullable' => true],
            ['name' => 'name', 'nullable' => false],
        ],
        'product_variants' => [
            ['name' => 'attribute_data', 'nullable' => true],
        ],
        'products' => [
            ['name' => 'attribute_data', 'nullable' => true],
        ],
        'transactions' => [
            ['name' => 'meta', 'nullable' => true],
        ],
        'media' => [
            ['name' => 'custom_properties', 'nullable' => false],
            ['name' => 'generated_conversions', 'nullable' => false],
            ['name' => 'manipulations', 'nullable' => false],
            ['name' => 'responsive_images', 'nullable' => false],
        ],
    ];

    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        foreach ($this->columnsToUpdate as $table => $columns) {
            $fullTableName = $this->getTableName($table);

            if (! Schema::hasTable($fullTableName)) {
                continue;
            }

            Schema::table($fullTableName, function (Blueprint $blueprint) use ($columns) {
                foreach ($columns as $column) {
                    $blueprint->jsonb($column['name'])
                        ->nullable($column['nullable'])
                        ->change();
                }
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        foreach ($this->columnsToUpdate as $table => $columns) {
            $fullTableName = $this->getTableName($table);

            if (! Schema::hasTable($fullTableName)) {
                continue;
            }

            Schema::table($fullTableName, function (Blueprint $blueprint) use ($columns) {
                foreach ($columns as $column) {
                    $blueprint->json($column['name'])
                        ->nullable($column['nullable'])
                        ->change();
                }
            });
        }
    }

    private function getTableName(string $table): string
    {
        return in_array($table, ['activity_log', 'media']) ? $table : $this->prefix.$table;
    }
};
