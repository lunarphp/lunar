<?php

namespace Lunar\Admin\Database\State;

use Illuminate\Support\Facades\Schema;
use Lunar\Admin\Support\Facades\LunarAccessControl;
use Lunar\Admin\Support\Facades\LunarPanel;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class EnsureBaseRolesAndPermissions
{
    public function prepare()
    {
        //
    }

    public function run()
    {
        try {
            $guard = LunarPanel::getPanel()->getAuthGuard();
        } catch (\Throwable $e) {
            // No admin panel registered — nothing to seed.
            return;
        }

        $tableNames = config('permission.table_names');

        if (Schema::hasTable($tableNames['roles'])) {
            foreach (LunarAccessControl::getBaseRoles() as $role) {
                Role::query()->firstOrCreate([
                    'name' => $role,
                    'guard_name' => $guard,
                ]);
            }
        }

        if (Schema::hasTable($tableNames['permissions'])) {
            foreach (LunarAccessControl::getBasePermissions() as $permission) {
                Permission::firstOrCreate([
                    'name' => $permission,
                    'guard_name' => $guard,
                ]);
            }
        }
    }
}
