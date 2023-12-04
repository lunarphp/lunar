<?php

namespace Lunar\Admin\Support\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Support\Collection getRoles(bool $refresh = false)
 * @method static \Illuminate\Support\Collection getPermissions(bool $refresh = false)
 * @method static \Illuminate\Support\Collection getGroupedPermissions(bool $refresh = false)
 * @method static void useRoleAsAdmin(string|array $roleHandle)
 * @method static \Illuminate\Support\Collection getAdmin()
 * @method static \Illuminate\Support\Collection getRolesWithoutAdmin(bool $refresh = false)
 *
 * @see \Lunar\Admin\Auth\Manifest
 */
class LunarAccessControl extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'lunar-access-control';
    }
}
