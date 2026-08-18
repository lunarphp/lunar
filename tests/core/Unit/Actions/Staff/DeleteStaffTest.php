<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Staff\DeleteStaff;
use Lunar\Core\Exceptions\StaffActionException;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('soft deletes a staff member', function () {
    Staff::factory()->create(['admin' => true]);
    $staff = Staff::factory()->create(['admin' => false]);

    app(DeleteStaff::class)->execute($staff);

    $this->assertSoftDeleted('lunar_staff', ['id' => $staff->id]);
});

test('refuses to delete the last admin', function () {
    $staff = Staff::factory()->create(['admin' => true]);

    expect(fn () => app(DeleteStaff::class)->execute($staff))
        ->toThrow(StaffActionException::class);

    expect($staff->refresh()->trashed())->toBeFalse();
});

test('deletes an admin when another admin remains', function () {
    Staff::factory()->create(['admin' => true]);
    $staff = Staff::factory()->create(['admin' => true]);

    app(DeleteStaff::class)->execute($staff);

    $this->assertSoftDeleted('lunar_staff', ['id' => $staff->id]);
});
