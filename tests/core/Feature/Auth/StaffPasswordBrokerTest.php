<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

test('the staff password broker is registered against the staff provider', function () {
    expect(config('auth.passwords.staff'))->toMatchArray([
        'provider' => 'staff',
        'expire' => 60,
        'throttle' => 60,
    ]);
});

test('the staff password broker issues and validates tokens', function () {
    $staff = Staff::factory()->create();

    $token = Password::broker('staff')->createToken($staff);

    expect(Password::broker('staff')->tokenExists($staff, $token))->toBeTrue();
});
