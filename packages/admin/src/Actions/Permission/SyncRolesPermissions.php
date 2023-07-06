<?php

namespace Lunar\Hub\Actions\Permission;

use Lunar\Hub\Auth\Manifest;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SyncRolesPermissions
{
    public function __invoke()
    {
        $manifest = app(Manifest::class);

        $permissions = $manifest->getPermissions();

        $guard = 'staff';

        foreach (['admin', 'staff'] as $roleName) {
            Role::query()->firstOrCreate([
                'name' => $roleName,
                'guard_name' => $guard,
            ]);
        }

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission->handle,
                'guard_name' => $guard,
            ]);
        }
    }
}
