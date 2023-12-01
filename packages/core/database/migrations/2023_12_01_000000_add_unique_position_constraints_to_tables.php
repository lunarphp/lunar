<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;
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
         * Model's with position 
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
                DB::table($model->getTable())
                    ->select(array_merge(
                        [$model->getKeyName()], 
                        $model->positionUniqueConstraints()
                    ))
                    ->orderBy('position')
                    ->orderBy('id')
                    ->get()
                    ->groupBy(fn (stdClass $row, int $key) =>
                        collect($row)
                            ->only($model->positionUniqueConstraints())
                            ->except('position')
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
                $uniqueIndex = $this->prefix . $table->getTable() . '_unique_position';
                $uniqueConstraints = array_merge($model->positionUniqueConstraints(), ['position']);
                $table->unsignedBigInteger('position')->default(null)->index()->change();
                if (!$schema->hasIndex($uniqueIndex)) {
                    $table->unique($uniqueConstraints, $uniqueIndex);
                }
            });
        }
    }
};