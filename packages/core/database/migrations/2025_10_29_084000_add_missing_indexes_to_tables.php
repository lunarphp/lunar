<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    /**
     * The list of tables and columns to index.
     */
    private $columnsToUpdate = [
        'channelables' => [
            'enabled',
            'starts_at',
        ],
        'products' => [
            'deleted_at',
        ],
        'product_variants' => [
            'deleted_at',
        ],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->columnsToUpdate as $table => $columns) {
            $fullTableName = $this->getTableName($table);

            Schema::table($fullTableName, function (Blueprint $tableBlueprint) use ($columns) {
                foreach ($columns as $column) {
                    $tableBlueprint->index($column);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->columnsToUpdate as $table => $columns) {
            $fullTableName = $this->getTableName($table);

            Schema::table($fullTableName, function (Blueprint $tableBlueprint) use ($columns) {
                foreach ($columns as $column) {
                    $tableBlueprint->dropIndex([$column]);
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
