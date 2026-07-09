<?php

use Illuminate\Support\Facades\Hash;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->staff = Staff::factory()->create(['admin' => true]);
    $this->actingAs($this->staff, 'staff');
});

test('the password can be updated with the current password', function () {
    $this->put(route('panel.account.password.update'), [
        'current_password' => 'password',
        'password' => 'new-secret-password',
        'password_confirmation' => 'new-secret-password',
    ])->assertRedirect()->assertSessionHas('success');

    expect(Hash::check('new-secret-password', $this->staff->fresh()->password))->toBeTrue();
});

test('the current password must match', function () {
    $this->put(route('panel.account.password.update'), [
        'current_password' => 'wrong-password',
        'password' => 'new-secret-password',
        'password_confirmation' => 'new-secret-password',
    ])->assertSessionHasErrors('current_password');

    expect(Hash::check('password', $this->staff->fresh()->password))->toBeTrue();
});
