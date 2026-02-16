<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Admin\Support\Facades\LunarPanel;
use Lunar\Base\Migration;
use Spatie\Permission\Models\Permission;

class AddCanManagePermission extends Migration
{
    public function up()
    {
        $guard = LunarPanel::getPanel()->getAuthGuard();

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
}
