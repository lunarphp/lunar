<?php

namespace Lunar\Core\Database\State;

use Illuminate\Support\Facades\Schema;
use Lunar\Core\Auth\Manifest;
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
        $manifest = app(Manifest::class);
        $guard = $manifest->getAuthGuard();

        $tableNames = config('permission.table_names');

        if (! $tableNames) {
            return;
        }

        if (Schema::hasTable($tableNames['roles'])) {
            foreach ($manifest->getBaseRoles() as $role) {
                Role::query()->firstOrCreate([
                    'name' => $role,
                    'guard_name' => $guard,
                ]);
            }
        }

        if (Schema::hasTable($tableNames['permissions'])) {
            foreach ($manifest->getBasePermissions() as $permission) {
                Permission::firstOrCreate([
                    'name' => $permission,
                    'guard_name' => $guard,
                ]);
            }
        }
    }
}
