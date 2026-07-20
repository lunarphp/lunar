<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Staff\CreateStaff;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Core\TestCase;
use Spatie\Permission\Models\Role;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('creates a staff member with the given attributes', function () {
    $staff = app(CreateStaff::class)->execute([
        'first_name' => 'Bob',
        'last_name' => 'Builder',
        'email' => 'bob@example.com',
        'password' => 'super-secret-password',
    ]);

    expect($staff)->toBeInstanceOf(Staff::class);

    $this->assertDatabaseHas('lunar_staff', [
        'id' => $staff->id,
        'email' => 'bob@example.com',
    ]);
});

test('assigns the given roles', function () {
    Role::create(['name' => 'estimator', 'guard_name' => 'staff']);

    $staff = app(CreateStaff::class)->execute([
        'first_name' => 'Bob',
        'last_name' => 'Builder',
        'email' => 'bob@example.com',
        'password' => 'super-secret-password',
        'roles' => ['estimator'],
    ]);

    expect($staff->hasRole('estimator'))->toBeTrue();
});
