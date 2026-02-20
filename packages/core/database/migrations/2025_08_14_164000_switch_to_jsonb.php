<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    /**
     * The list of tables and JSON columns to update.
     */
    private $columnsToUpdate = [
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

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Only run for PostgreSQL - MySQL has no change and SQLite will error when trying to change
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        foreach ($this->columnsToUpdate as $table => $columns) {
            $fullTableName = $this->getTableName($table);

            Schema::table($fullTableName, function (Blueprint $tableBlueprint) use ($columns) {
                foreach ($columns as $column) {
                    $tableBlueprint->jsonb($column['name'])
                        ->nullable($column['nullable'])
                        ->change();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Only run for PostgreSQL
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        foreach ($this->columnsToUpdate as $table => $columns) {
            $fullTableName = $this->getTableName($table);

            Schema::table($fullTableName, function (Blueprint $tableBlueprint) use ($columns) {
                foreach ($columns as $column) {
                    $tableBlueprint->json($column['name'])
                        ->nullable($column['nullable'])
                        ->change();
                }
            });
        }
    }

    /**
     * Get the full table name with appropriate prefix.
     */
    private function getTableName(string $table): string
    {
        return in_array($table, ['activity_log', 'media']) ? $table : $this->prefix.$table;
    }
};
