<?php

use Illuminate\Support\Facades\Schema;
use Lunar\Admin\Support\Facades\LunarPanel;
use Lunar\Base\Migration;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    public function up()
    {
        try {
            $guard = LunarPanel::getPanel()->getAuthGuard();
        } catch (Exception $e) {
            return;
        }

        $tableNames = config('permission.table_names');

        if (! Schema::hasTable($tableNames['permissions'])) {
            return;
        }

        Permission::firstOrCreate([
            'name' => 'shipping:manage',
            'guard_name' => $guard,
        ]);
    }

    public function down()
    {
        $tableNames = config('permission.table_names');

        if (! Schema::hasTable($tableNames['permissions'])) {
            return;
        }

        Permission::query()->where('name', 'shipping:manage')->delete();
    }
};
