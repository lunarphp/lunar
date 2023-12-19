<?php

use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\StaffResource;
use Lunar\Admin\Filament\Resources\StaffResource\Pages\AccessControl;
use Lunar\Admin\Filament\Resources\StaffResource\Pages\ListStaff;
use Lunar\Admin\Support\DataTransferObjects\Permission;
use Lunar\Admin\Support\Facades\LunarAccessControl;
use Lunar\Admin\Support\Facades\LunarPanel;
use Spatie\Permission\Models\Role;

uses(\Lunar\Tests\Admin\Feature\Filament\TestCase::class)
    ->group('resource.staff');

beforeEach(fn () => $this->asStaff(admin: true));

it('can render acl page', function () {
    $this->get(StaffResource::getUrl('acl'))
        ->assertSuccessful();

    Livewire::test(ListStaff::class)
        ->assertActionHasUrl('access-control', StaffResource::getUrl('acl'));
});

it('can add role', function () {
    $guard = LunarPanel::getPanel()->getAuthGuard();

    $roles = LunarAccessControl::getRolesWithoutAdmin();

    Livewire::test(AccessControl::class)
        ->assertSee($roles->first()->transLabel)
        ->callAction('add_role', [
            'name' => 'new_role',
        ])
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas(Role::class, [
        'name' => 'new_role',
        'guard_name' => $guard,
    ]);

});

it('can delete role', function () {
    $guard = LunarPanel::getPanel()->getAuthGuard();

    $roleData = [
        'name' => 'role_one',
        'guard_name' => $guard,
    ];

    $role = Role::create($roleData);

    $roleCount = Role::where('guard_name', $guard)->count();

    Livewire::test(AccessControl::class)
        ->assertSee($role->name)
        ->callAction('deleteRole', arguments: [
            'handle' => $role->name,
        ])
        ->assertHasNoActionErrors();

    $this->assertDataBaseCount(Role::class, $roleCount - 1);
    $this->assertDatabaseMissing(Role::class, $roleData);
});

it('can set permission to role', function () {
    $guard = LunarPanel::getPanel()->getAuthGuard();

    $roles = LunarAccessControl::getRolesWithoutAdmin();

    $role = $roles->first();
    $roleModel = Role::findByName($role->handle);

    $permissions = LunarAccessControl::getGroupedPermissions();
    /** @var Permission $perm */
    $perm = $permissions->first(fn (Permission $perm) => $perm->children->count());

    $childPerm = $perm->children->random(1)->first();

    Livewire::test(AccessControl::class)
        ->assertSee($role->transLabel)
        ->set([
            "state.{$role->handle}#{$perm->handle}" => true,
            "state.{$role->handle}#{$childPerm->handle}" => true,
        ])
        ->assertSet("state.{$role->handle}#{$perm->handle}", true)
        ->assertSet("state.{$role->handle}#{$childPerm->handle}", true);

    expect([
        $perm->handle,
        $childPerm->handle,
    ])
        ->toEqualCanonicalizing($roleModel->refresh()->getPermissionNames()->toArray());
});
