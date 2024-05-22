<?php

use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\StaffResource;
use Lunar\Admin\Filament\Resources\StaffResource\Pages\EditStaff;
use Lunar\Admin\Models\Staff;
use Lunar\Admin\Support\Facades\LunarAccessControl;
use Spatie\Permission\Models\Role;

uses(\Lunar\Tests\Admin\Feature\Filament\TestCase::class)
    ->group('resource.staff');

beforeEach(fn () => $this->asStaff(admin: true));

it('can render staff edit page', function () {
    $this->get(StaffResource::getUrl('edit', ['record' => Staff::factory()->create()]))
        ->assertSuccessful();
});

it('can retrieve staff data', function () {
    $staff = Staff::factory()->create();

    Livewire::test(EditStaff::class, [
        'record' => $staff->getRouteKey(),
    ])
        ->assertFormSet([
            'firstname' => $staff->firstname,
            'lastname' => $staff->lastname,
            'email' => $staff->email,
        ]);
});

it('can save staff data', function () {
    $staff = Staff::factory()->create();

    $newData = Staff::factory()->make();

    Livewire::test(EditStaff::class, [
        'record' => $staff->getRouteKey(),
    ])
        ->fillForm([
            'firstname' => $newData->firstname,
            'lastname' => $newData->lastname,
            'email' => $newData->email,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($staff->refresh())
        ->firstname->toBe($newData->firstname)
        ->lastname->toBe($newData->lastname)
        ->email->toBe($newData->email);
});

it('can assign staff role and permissions', function () {
    $staff = Staff::factory()->create();

    $roles = ['staff'];
    $permissions = LunarAccessControl::getGroupedPermissions()->random(4)->mapWithKeys(fn ($perm) => [$perm->handle => true]);
    $rolePermission = array_keys($permissions->take(1)->toArray());

    $staffRole = Role::findByName('staff');
    $staffRole->syncPermissions($rolePermission);

    Livewire::test(EditStaff::class, [
        'record' => $staff->getRouteKey(),
    ])
        ->fillForm([
            'roles' => $roles,
            'permissions' => $permissions->toArray(),
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $result = $staff->hasExactRoles($roles);

    // C

    if ($result) {
        var_dump($roles);
        var_dump($staff->roles->pluck('name'));
    }
    expect($result)
        ->toBeTrue();

    // check assigned permissions does not include role's permissions
    expect($permissions->reject(fn ($val, $handle) => $handle == $rolePermission)->toArray())
        ->toEqualCanonicalizing($staff->getDirectPermissions()->pluck('name')->toArray());

    // check role's permission
    expect($rolePermission)
        ->toEqualCanonicalizing($staff->getPermissionsViaRoles()->pluck('name')->toArray());
});
