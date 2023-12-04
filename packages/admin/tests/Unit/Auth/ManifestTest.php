<?php

use Lunar\Admin\Auth\Manifest;
use Lunar\Admin\Support\Facades\LunarPanel;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(\Lunar\Admin\Tests\Feature\Filament\TestCase::class)
    ->group('unit.manifest');

beforeEach(fn () => $this->manifest = new Manifest);

test('manifest can get roles', function () {
    $roles = $this->manifest->getRoles();

    expect($roles)
        ->toBeIterable()
        ->not->toBeEmpty();
});

test('manifest can get refreshed roles', function () {
    $roles = $this->manifest->getRoles()->pluck('handle')->toArray();

    Role::create([
        'name' => 'role_one',
        'guard_name' => LunarPanel::getPanel()->getAuthGuard(),
    ]);

    $cachedRoles = $this->manifest->getRoles()->pluck('handle')->toArray();
    $refreshedRoles = $this->manifest->getRoles(refresh: true)->pluck('handle')->toArray();

    expect($roles)
        ->toEqualCanonicalizing($cachedRoles);

    expect($refreshedRoles)
        ->toContain('role_one');

    expect($refreshedRoles)
        ->toMatchArray($roles)
        ->not->toEqualCanonicalizing($roles);
});

test('manifest can get permissions', function () {
    $permissions = $this->manifest->getPermissions();

    expect($permissions)
        ->toBeIterable()
        ->not->toBeEmpty();
});

test('manifest can get refreshed permissions', function () {
    $permissions = $this->manifest->getPermissions()->pluck('handle')->toArray();

    Permission::create([
        'name' => 'perm_one',
        'guard_name' => LunarPanel::getPanel()->getAuthGuard(),
    ]);

    $cachedPermissions = $this->manifest->getPermissions()->pluck('handle')->toArray();
    $refreshedPermissions = $this->manifest->getPermissions(refresh: true)->pluck('handle')->toArray();

    expect($permissions)
        ->toEqualCanonicalizing($cachedPermissions);

    expect($refreshedPermissions)
        ->toContain('perm_one');

    expect($refreshedPermissions)
        ->toMatchArray($permissions)
        ->not->toEqualCanonicalizing($permissions);
});

test('manifest can get grouped permissions', function () {
    $permissions = $this->manifest->getGroupedPermissions();

    expect($permissions)
        ->toBeIterable()
        ->not->toBeEmpty();

    $parent = $permissions->first(fn ($perm) => count($perm->children));

    expect($parent->children)
        ->toBeIterable()
        ->not->toBeEmpty();

    $notParent = $permissions->first(fn ($perm) => ! count($perm->children));

    expect($notParent->children)
        ->toBeIterable()
        ->toBeEmpty();
});

test('manifest can get refreshed grouped permissions', function () {
    $guard = LunarPanel::getPanel()->getAuthGuard();
    foreach ([
        'group',
        'group:child_1',
    ] as $perm) {
        Permission::create([
            'name' => $perm,
            'guard_name' => $guard,
        ]);
    }

    $permissions = $this->manifest->getGroupedPermissions()->first(fn ($perm) => $perm->handle == 'group')->children->pluck('handle')->toArray();

    Permission::create([
        'name' => 'group:child_2',
        'guard_name' => $guard,
    ]);

    $cachedPermissions = $this->manifest->getGroupedPermissions()->first(fn ($perm) => $perm->handle == 'group')->children->pluck('handle')->toArray();

    $refreshedPermissions = $this->manifest->getGroupedPermissions(refresh: true)->first(fn ($perm) => $perm->handle == 'group')->children->pluck('handle')->toArray();

    expect($permissions)
        ->toEqualCanonicalizing($cachedPermissions);

    expect($refreshedPermissions)
        ->toContain('group:child_2');

    expect($refreshedPermissions)
        ->toMatchArray($permissions)
        ->not->toEqualCanonicalizing($permissions);
});

test('manifest can set admin', function () {
    $currentAdmin = $this->manifest->getAdmin();

    $this->manifest->useRoleAsAdmin('super_admin');

    $newAdmin = $this->manifest->getAdmin();

    expect($currentAdmin->toArray())
        ->not->toEqualCanonicalizing($newAdmin->toArray());

    expect($newAdmin)
        ->not->toMatchArray($currentAdmin);

});

test('manifest can get roles without admin', function () {
    $admin = $this->manifest->getAdmin();

    $rolesWithAdmin = $this->manifest->getRoles();
    $rolesWithoutAdmin = $this->manifest->getRolesWithoutAdmin();

    expect($rolesWithAdmin->pluck('handle')->toArray())
        ->toMatchArray($admin->toArray());

    expect($rolesWithoutAdmin->pluck('handle')->toArray())
        ->not->toMatchArray($admin->toArray());
});
