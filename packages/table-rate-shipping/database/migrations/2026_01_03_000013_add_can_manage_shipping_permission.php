<?php

use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;
use Lunar\Core\Support\Facades\LunarAccessControl;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    public function up()
    {
        // Resolved through core so the permission is seeded whichever admin
        // (Filament, the Inertia panel, or neither) is installed.
        $guard = LunarAccessControl::getAuthGuard();

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
