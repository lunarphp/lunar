<?php

use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

it('redirects to the first settings page the user can see', function () {
    $staff = Staff::factory()->create(['admin' => true]);

    $this->actingAs($staff, 'staff')
        ->get('/panel/settings')
        ->assertRedirect(route('panel.settings.staff.index'));
});

it('falls back to the dashboard when no settings page is visible', function () {
    $staff = Staff::factory()->create(['admin' => false]);

    $this->actingAs($staff, 'staff')
        ->get('/panel/settings')
        ->assertRedirect(route('panel.dashboard'));
});

it('redirects guests to the login screen', function () {
    $this->get('/panel/settings')->assertRedirect(route('panel.login'));
});
