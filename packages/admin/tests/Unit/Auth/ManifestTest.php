<?php

namespace Lunar\Hub\Tests\Unit\Auth;

use Lunar\Hub\Auth\Manifest;
use Lunar\Hub\Auth\Permission;
use Lunar\Hub\Tests\TestCase;

/**
 * @group hub.auth
 */
class ManifestTest extends TestCase
{
    /** @test */
    public function can_list_permissions()
    {
        $manifest = $this->app->make(Manifest::class);

        $permissions = $manifest->getPermissions();

        $this->assertIsIterable($permissions);
        $this->assertContainsOnlyInstancesOf(Permission::class, $permissions);
        $this->assertNotEmpty($permissions);
    }

    /** @test */
    public function can_add_permission()
    {
        $manifest = $this->app->make(Manifest::class);

        $manifest->addPermission(function (Permission $permission) {
            $permission->name('Test Permission')->handle('test-permission')->description('Test!');
        });

        $permission = $manifest->getPermissions()->first(fn ($p) => $p->handle === 'test-permission');

        $this->assertNotNull($permission);
    }

    /** @test */
    public function can_add_permission_as_child_of_another()
    {
        $manifest = $this->app->make(Manifest::class);

        $manifest->addPermission(function (Permission $permission) {
            $permission->name('Parent')->handle('test')->description('Parent');
        });

        $manifest->addPermission(function (Permission $permission) {
            $permission->name('Child')->handle('test:child')->description('Test Child!');
        });

        $parent = $manifest->getGroupedPermissions()->first(fn ($p) => $p->handle === 'test');

        $this->assertNotEmpty($parent->children);

        $child = $parent->children->first(fn ($c) => $c->handle === 'test:child');

        $this->assertNotNull($child);
    }
}
