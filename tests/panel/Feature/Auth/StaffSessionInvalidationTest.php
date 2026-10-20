<?php

use Illuminate\Support\Facades\Hash;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

/*
 * A staff member who changes or resets their password after a suspected
 * compromise must not leave another browser signed in to the panel.
 */
beforeEach(function () {
    $this->staff = Staff::factory()->create(['admin' => true]);
});

test('a panel session ends once the staff password changes elsewhere', function () {
    $this->actingAs($this->staff, 'staff')
        ->get(route('panel.dashboard'))
        ->assertOk()
        ->assertSessionHas('password_hash_staff');

    // Another device (or a reset) changes the password.
    $this->staff->forceFill(['password' => 'changed-elsewhere-password'])->save();

    $this->get(route('panel.dashboard'))->assertRedirect(route('panel.login'));

    $this->assertGuest('staff');
});

test('changing the password keeps the session that changed it', function () {
    $this->actingAs($this->staff, 'staff')->get(route('panel.dashboard'))->assertOk();

    $this->put(route('panel.account.password.update'), [
        'current_password' => 'password',
        'password' => 'new-secret-password',
        'password_confirmation' => 'new-secret-password',
    ])->assertRedirect()->assertSessionHas('success');

    $this->get(route('panel.dashboard'))->assertOk();
    $this->assertAuthenticatedAs($this->staff->fresh(), 'staff');
});

test('changing the password rotates the remember token', function () {
    $this->staff->forceFill(['remember_token' => 'old-remember-token'])->save();

    $this->actingAs($this->staff, 'staff')->put(route('panel.account.password.update'), [
        'current_password' => 'password',
        'password' => 'new-secret-password',
        'password_confirmation' => 'new-secret-password',
    ])->assertSessionHas('success');

    $staff = $this->staff->fresh();

    expect($staff->remember_token)->not->toBe('old-remember-token')
        ->and(Hash::check('new-secret-password', $staff->password))->toBeTrue();
});

test('the storefront password hash is left alone', function () {
    $this->actingAs($this->staff, 'staff')
        ->get(route('panel.dashboard'))
        ->assertSessionMissing('password_hash_web');
});
