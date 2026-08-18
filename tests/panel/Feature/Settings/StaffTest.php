<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(TestCase::class);

beforeEach(function () {
    // Pin the email: the search test queries email LIKE %term%, and an
    // unconstrained faker email on the acting staff can contain the term.
    $this->staff = Staff::factory()->create(['admin' => true, 'first_name' => 'Ada', 'last_name' => 'Admin', 'email' => 'ada.admin@example.com']);
    $this->actingAs($this->staff, 'staff');
});

test('the staff index renders with the real staff list', function () {
    Staff::factory()->create(['first_name' => 'Bob', 'last_name' => 'Builder', 'admin' => false]);

    $this->get(route('panel.settings.staff.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/staff/Index')
            ->has('staff.data', 2)
            ->where('staff.data.0.full_name', 'Ada Admin')
            ->where('staff.data.0.admin', true)
            ->where('staff.data.1.full_name', 'Bob Builder')
            ->has('urls.store')
        );
});

test('the staff index paginates at 25 per page', function () {
    Staff::factory()->count(30)->create();

    $this->get(route('panel.settings.staff.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('staff.data', 25)
            ->where('staff.total', 31)
            ->where('staff.last_page', 2)
        );
});

test('the staff index can be searched', function () {
    Staff::factory()->create(['first_name' => 'Bob', 'last_name' => 'Builder', 'email' => 'bob@example.com']);

    $this->get(route('panel.settings.staff.index', ['q' => 'bob']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('staff.data', 1)
            ->where('staff.data.0.first_name', 'Bob')
        );
});

test('row actions omit delete for yourself and the last admin', function () {
    // Ada (acting, sole admin, first by name) and Bob (deletable).
    Staff::factory()->create(['first_name' => 'Bob', 'last_name' => 'Builder', 'admin' => false]);

    $this->get(route('panel.settings.staff.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('staff.data.0._actions', fn ($actions) => isset($actions['edit']) && ! isset($actions['delete']))
            ->where('staff.data.1._actions', fn ($actions) => isset($actions['edit'], $actions['delete']))
        );
});

test('a staff member can be created with roles', function () {
    Role::create(['name' => 'estimator', 'guard_name' => 'staff']);

    $this->post(route('panel.settings.staff.store'), [
        'first_name' => 'Bob',
        'last_name' => 'Builder',
        'email' => 'bob@example.com',
        'password' => 'super-secret-password',
        'roles' => ['estimator'],
    ])->assertRedirect(route('panel.settings.staff.index'))
        ->assertSessionHas('success');

    $staff = Staff::where('email', 'bob@example.com')->first();

    expect($staff)->not->toBeNull();
    expect($staff->admin)->toBeFalse();
    expect($staff->hasRole('estimator'))->toBeTrue();
});

test('email must be unique', function () {
    Staff::factory()->create(['email' => 'bob@example.com']);

    $this->post(route('panel.settings.staff.store'), [
        'first_name' => 'Bob',
        'last_name' => 'Builder',
        'email' => 'bob@example.com',
        'password' => 'super-secret-password',
    ])->assertSessionHasErrors('email');
});

test('the staff edit screen renders with the staff data', function () {
    $member = Staff::factory()->create(['first_name' => 'Bob', 'last_name' => 'Builder', 'admin' => false]);

    $this->get(route('panel.settings.staff.edit', $member))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/staff/Edit')
            ->where('staff.first_name', 'Bob')
            ->where('isSelf', false)
            ->where('isLastAdmin', false)
            ->has('urls.update')
        );
});

test('a staff member can be updated without changing the password', function () {
    $member = Staff::factory()->create(['first_name' => 'Bob', 'last_name' => 'Builder', 'admin' => false]);
    $originalPassword = $member->password;

    $this->put(route('panel.settings.staff.update', $member), [
        'first_name' => 'Robert',
        'last_name' => 'Builder',
        'email' => $member->email,
        'password' => null,
    ])->assertRedirect(route('panel.settings.staff.index'))
        ->assertSessionHas('success');

    $member->refresh();

    expect($member->first_name)->toBe('Robert');
    expect($member->password)->toBe($originalPassword);
});

test('updating roles replaces the previous set', function () {
    Role::create(['name' => 'estimator', 'guard_name' => 'staff']);
    Role::create(['name' => 'packer', 'guard_name' => 'staff']);

    $member = Staff::factory()->create(['admin' => false]);
    $member->syncRoles(['estimator']);

    $this->put(route('panel.settings.staff.update', $member), [
        'first_name' => $member->first_name,
        'last_name' => $member->last_name,
        'email' => $member->email,
        'roles' => ['packer'],
    ])->assertRedirect(route('panel.settings.staff.index'));

    $member->refresh();

    expect($member->hasRole('packer'))->toBeTrue();
    expect($member->hasRole('estimator'))->toBeFalse();
});

test('the last admin cannot lose the admin flag', function () {
    $this->from(route('panel.settings.staff.edit', $this->staff))
        ->put(route('panel.settings.staff.update', $this->staff), [
            'first_name' => 'Ada',
            'last_name' => 'Admin',
            'email' => $this->staff->email,
            'admin' => false,
        ])->assertRedirect(route('panel.settings.staff.edit', $this->staff))
        ->assertSessionHas('error', __('panel::staff.last_admin_blocked'));

    expect($this->staff->fresh()->admin)->toBeTrue();
});

test('you cannot delete your own account', function () {
    $this->from(route('panel.settings.staff.edit', $this->staff))
        ->delete(route('panel.settings.staff.destroy', $this->staff))
        ->assertRedirect(route('panel.settings.staff.edit', $this->staff))
        ->assertSessionHas('error', __('panel::staff.delete_blocked_self'));

    expect(Staff::find($this->staff->id))->not->toBeNull();
});

test('the last admin cannot be deleted by another staff member', function () {
    $manager = Staff::factory()->create(['admin' => false]);
    $manager->givePermissionTo(app(PermissionRegistrar::class)
        ->getPermissionClass()::findOrCreate('settings:manage-staff', 'staff'));

    $this->actingAs($manager, 'staff');

    $this->from(route('panel.settings.staff.edit', $this->staff))
        ->delete(route('panel.settings.staff.destroy', $this->staff))
        ->assertRedirect(route('panel.settings.staff.edit', $this->staff))
        ->assertSessionHas('error', __('panel::staff.delete_blocked_last_admin'));

    expect(Staff::find($this->staff->id))->not->toBeNull();
});

test('a staff member can be deleted', function () {
    $member = Staff::factory()->create(['admin' => false]);

    $this->delete(route('panel.settings.staff.destroy', $member))
        ->assertRedirect(route('panel.settings.staff.index'))
        ->assertSessionHas('success');

    expect(Staff::find($member->id))->toBeNull();
    expect(Staff::withTrashed()->find($member->id))->not->toBeNull();
});
