-<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\BaseModel;
use Lunar\Base\Migration;
use Spatie\StructureDiscoverer\Discover;

class RemapPolymorphicRelations extends Migration
{
    public function up()
    {
        // TODO: Copy to shipping table.

        $modelClasses = collect(
            Discover::in(__DIR__.'/../../src/Models')
                ->classes()
                ->extending(BaseModel::class)
                ->get()
        )->mapWithKeys(
            fn ($class) => [
                $class => \Illuminate\Support\Str::snake(class_basename($class))
            ]
        );

        $tables = [
            'attributables' => ['attributable_type'],
            'cart_lines' => ['purchasable_type'],
            'channelables' => ['channelable_type'],
            'discount_purchasables' => ['purchasable_type'],
            'order_lines' => ['purchasable_type'],
            'prices' => ['priceable_type'],
            'taggables' => ['taggable_type'],
            'urls' => ['element_type'],
        ];

        $activityLog = \Illuminate\Support\Facades\DB::table('activity_log');
        $media = \Illuminate\Support\Facades\DB::table('media');
        $permissions = \Illuminate\Support\Facades\DB::table('model_has_permissions');
        $roles = \Illuminate\Support\Facades\DB::table('model_has_roles');

        foreach ($modelClasses as $modelClass => $mapping) {

            $activityLog->where('subject_type', '=', $modelClass)->update([
                'subject_type' => $mapping
            ]);

            $media->where('model_type', '=', $modelClass)->update([
                'model_type' => $mapping
            ]);

            $permissions->where('model_type', '=', $modelClass)->update([
                'model_type' => $mapping
            ]);

            $roles->where('model_type', '=', $modelClass)->update([
                'model_type' => $mapping
            ]);

            foreach ($tables as $tableName => $columns) {
                $table = \Illuminate\Support\Facades\DB::table(
                    $this->prefix.$tableName
                );

                foreach ($columns as $column) {
                    $table->where($column, '=', $modelClass)->update([
                        $column => $mapping
                    ]);
                }
            }
        }
    }

    public function down()
    {
        Schema::table($this->prefix.'prices', function (Blueprint $table) {
            $table->renameColumn('min_quantity', 'tier');
        });
    }
}
