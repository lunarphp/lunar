<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Staff\UpdateStaff;
use Lunar\Core\Exceptions\StaffActionException;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Core\TestCase;
use Spatie\Permission\Models\Role;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('updates the staff attributes', function () {
    $staff = Staff::factory()->create(['first_name' => 'Old']);

    app(UpdateStaff::class)->execute($staff, ['first_name' => 'New']);

    $this->assertDatabaseHas('lunar_staff', [
        'id' => $staff->id,
        'first_name' => 'New',
    ]);
});

test('keeps the current password when none is supplied', function () {
    $staff = Staff::factory()->create();
    $original = $staff->password;

    app(UpdateStaff::class)->execute($staff, ['first_name' => 'New', 'password' => null]);

    expect($staff->refresh()->password)->toBe($original);
});

test('replaces roles when supplied', function () {
    Role::create(['name' => 'estimator', 'guard_name' => 'staff']);
    Role::create(['name' => 'packer', 'guard_name' => 'staff']);

    $staff = Staff::factory()->create(['admin' => false]);
    $staff->syncRoles(['estimator']);

    app(UpdateStaff::class)->execute($staff, ['roles' => ['packer']]);

    expect($staff->refresh()->hasRole('packer'))->toBeTrue()
        ->and($staff->hasRole('estimator'))->toBeFalse();
});

test('refuses to remove the admin flag from the last admin', function () {
    $staff = Staff::factory()->create(['admin' => true]);

    expect(fn () => app(UpdateStaff::class)->execute($staff, ['admin' => false]))
        ->toThrow(StaffActionException::class);
});

test('allows removing the admin flag when another admin remains', function () {
    Staff::factory()->create(['admin' => true]);
    $staff = Staff::factory()->create(['admin' => true]);

    app(UpdateStaff::class)->execute($staff, ['admin' => false]);

    expect($staff->refresh()->admin)->toBeFalse();
});
