<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(TestCase::class);

beforeEach(function () {
    $this->staff = Staff::factory()->create(['admin' => true]);
    $this->actingAs($this->staff, 'staff');
});

test('the roles index renders staff-guard roles with counts', function () {
    $role = Role::create(['name' => 'estimator', 'guard_name' => 'staff']);
    $role->givePermissionTo(Permission::findOrCreate('settings:core', 'staff'));
    Role::create(['name' => 'web-only', 'guard_name' => 'web']);

    Staff::factory()->create(['admin' => false])->assignRole('estimator');

    // The base admin and staff roles are seeded by the migrations.
    $this->get(route('panel.settings.roles.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/roles/Index')
            ->has('roles.data', 3)
            ->where('roles.data.0.name', 'admin')
            ->where('roles.data.0.firstParty', true)
            ->where('roles.data.1.name', 'estimator')
            ->where('roles.data.1.permissions_count', 1)
            ->where('roles.data.1.staff_count', 1)
            ->where('roles.data.1.firstParty', false)
            ->has('urls.store')
        );
});

test('roles carry first-party row actions, with delete omitted for protected roles', function () {
    $held = Role::create(['name' => 'estimator', 'guard_name' => 'staff']);
    Staff::factory()->create(['admin' => false])->assignRole($held);
    Role::create(['name' => 'unused', 'guard_name' => 'staff']);

    // Ordered by name: admin (base), estimator (held), staff (base), unused.
    $this->get(route('panel.settings.roles.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('tableActions', fn ($actions) => collect($actions)->pluck('key')->all() === ['edit', 'delete'])
            ->where('roles.data.0._actions', fn ($actions) => isset($actions['edit']) && ! isset($actions['delete']))
            ->where('roles.data.1._actions', fn ($actions) => isset($actions['edit']) && ! isset($actions['delete']))
            ->where('roles.data.3._actions', fn ($actions) => isset($actions['edit'], $actions['delete']))
        );
});

test('a role can be created and redirects to its edit screen', function () {
    $this->post(route('panel.settings.roles.store'), [
        'name' => 'Catalogue Manager',
    ])->assertRedirect()
        ->assertSessionHas('success');

    $role = Role::where('name', 'catalogue-manager')->first();

    expect($role)->not->toBeNull();
    expect($role->guard_name)->toBe('staff');
});

test('a role name that slugs to an existing role is rejected', function () {
    Role::create(['name' => 'store-manager', 'guard_name' => 'staff']);

    $this->post(route('panel.settings.roles.store'), [
        'name' => 'Store Manager',
    ])->assertSessionHasErrors('name');

    expect(Role::where('name', 'store-manager')->where('guard_name', 'staff')->count())->toBe(1);
});

test('the role edit screen renders the grouped permission matrix', function () {
    $role = Role::create(['name' => 'estimator', 'guard_name' => 'staff']);
    Permission::findOrCreate('settings', 'staff');
    Permission::findOrCreate('settings:core', 'staff');
    $role->givePermissionTo('settings:core');

    $this->get(route('panel.settings.roles.edit', $role))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/roles/Edit')
            ->where('role.name', 'estimator')
            ->where('role.permissions.0', 'settings:core')
            ->has('permissionGroups')
            ->has('urls.update')
        );
});

test('updating a role syncs its permissions', function () {
    $role = Role::create(['name' => 'estimator', 'guard_name' => 'staff']);
    Permission::findOrCreate('settings', 'staff');
    Permission::findOrCreate('settings:core', 'staff');
    $role->givePermissionTo('settings');

    $this->put(route('panel.settings.roles.update', $role), [
        'permissions' => ['settings:core'],
    ])->assertRedirect()
        ->assertSessionHas('success');

    $role->refresh();

    expect($role->hasPermissionTo('settings:core'))->toBeTrue();
    expect($role->hasPermissionTo('settings'))->toBeFalse();
});

test('a base role cannot be deleted', function () {
    $role = Role::findByName('admin', 'staff');

    $this->from(route('panel.settings.roles.edit', $role))
        ->delete(route('panel.settings.roles.destroy', $role))
        ->assertRedirect(route('panel.settings.roles.edit', $role))
        ->assertSessionHas('error', __('panel::roles.delete_blocked_first_party'));

    expect(Role::find($role->id))->not->toBeNull();
});

test('a role held by staff cannot be deleted', function () {
    $role = Role::create(['name' => 'estimator', 'guard_name' => 'staff']);
    Staff::factory()->create(['admin' => false])->assignRole($role);

    $this->from(route('panel.settings.roles.edit', $role))
        ->delete(route('panel.settings.roles.destroy', $role))
        ->assertRedirect(route('panel.settings.roles.edit', $role))
        ->assertSessionHas('error', __('panel::roles.delete_blocked_staff'));

    expect(Role::find($role->id))->not->toBeNull();
});

test('an unused role can be deleted', function () {
    $role = Role::create(['name' => 'estimator', 'guard_name' => 'staff']);

    $this->delete(route('panel.settings.roles.destroy', $role))
        ->assertRedirect(route('panel.settings.roles.index'))
        ->assertSessionHas('success');

    expect(Role::find($role->id))->toBeNull();
});

test('roles from other guards are not reachable', function () {
    $role = Role::create(['name' => 'web-only', 'guard_name' => 'web']);

    $this->get(route('panel.settings.roles.edit', $role))->assertNotFound();
});
