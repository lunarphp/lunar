<?php

namespace Lunar\Admin\Support\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Lunar\Admin\Auth\Manifest;

/**
 * @method static Collection getRoles(bool $refresh = false)
 * @method static Collection getPermissions(bool $refresh = false)
 * @method static Collection getGroupedPermissions(bool $refresh = false)
 * @method static array getBaseRoles()
 * @method static array getBasePermissions()
 * @method static void useRoleAsAdmin(array|string $roleHandle)
 * @method static Collection getAdmin()
 * @method static Collection getRolesWithoutAdmin(bool $refresh = false)
 *
 * @see Manifest
 */
class LunarAccessControl extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'lunar-access-control';
    }
}
