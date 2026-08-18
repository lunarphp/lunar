<?php

use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;
use Spatie\Permission\Models\Permission;

uses(TestCase::class);

it('redirects guests to the panel login', function () {
    $this->get('/panel')->assertRedirect(route('panel.login'));
});

it('renders the dashboard for authenticated staff', function () {
    $staff = Staff::factory()->create(['admin' => true]);

    $this->actingAs($staff, 'staff')
        ->get('/panel')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('auth.user.email', $staff->email)
            ->where('auth.user.admin', true)
            ->where('navigation.items.0.key', 'dashboard')
            ->has('settingsNavigation')
            ->has('slots')
        );
});

it('shares an absolute dashboard url in navigation', function () {
    $staff = Staff::factory()->create(['admin' => true]);

    $this->actingAs($staff, 'staff')
        ->get('/panel')
        ->assertInertia(fn ($page) => $page
            ->where('navigation.items.0.url', route('panel.dashboard'))
        );
});

it('grants manifest permissions to admin staff via the gate', function () {
    $handle = app('lunar-access-control')->getPermissions()->first()->handle;

    $admin = Staff::factory()->create(['admin' => true]);
    $mortal = Staff::factory()->create(['admin' => false]);

    Permission::findOrCreate($handle, 'staff');

    expect($admin->can($handle))->toBeTrue()
        ->and($mortal->can($handle))->toBeFalse();

    $mortal->givePermissionTo($handle);
    expect($mortal->fresh()->can($handle))->toBeTrue();
});
