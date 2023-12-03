<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;
use Lunar\Facades\PositionManifest;
use Lunar\Models\Attribute;
use Lunar\Models\AttributeGroup;
use Lunar\Models\ProductOption;
use Lunar\Models\ProductOptionValue;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /**
         * Model's with `HasPosition` trait 
         */
        $models = [
            Attribute::class,
            AttributeGroup::class,
            ProductOption::class,
            ProductOptionValue::class,
        ];
        /**
         * Ensure that all position values are unique 
         */
        foreach ($models as $model) {
            $model = app($model);
            Schema::table($model->getTable(), function (Blueprint $table) use ($model) {
                DB::table($table->getTable())
                    ->select(array_merge(
                        [$model->getKeyName()], 
                        PositionManifest::constraints($model)
                    ))
                    ->orderBy('position')
                    ->orderBy('id')
                    ->get()
                    ->groupBy(fn (stdClass $row, int $key) =>
                        collect($row)
                            ->except([$model->getKeyName(), 'position'])
                            ->join('-')
                    )->each
                        ->each(fn(stdClass $row, int $key) => $row->position = $key + 1);
            });
        }
        /**
         * Add unique position index under consideration of 
         * the model's position constraints
         */
        foreach ($models as $model) {
            $model = app($model);
            Schema::table($model->getTable(), function (Blueprint $table) use ($model) {
                $schema = Schema::getConnection()
                    ->getDoctrineSchemaManager()
                    ->introspectTable($model->getTable());
                $table->unsignedBigInteger('position')
                    ->nullable(false)
                    ->default(null)
                    ->change();
                $index = $model->getTable() . '_position_index';
                if ($schema->hasIndex($index)) {
                    $table->dropIndex($index);
                }
                $index = $model->getTable() . '_position_unique';
                if ($schema->hasIndex($index)) {
                    $table->dropIndex($index);
                }
                $table->unique(PositionManifest::constraints($model), $index);
            });
        }
    }
};