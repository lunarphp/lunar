<?php

use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\StaffResource;
use Lunar\Admin\Filament\Resources\StaffResource\Pages\CreateStaff;
use Lunar\Admin\Models\Staff;

uses(\Lunar\Tests\Admin\Feature\Filament\TestCase::class)
    ->group('resource.staff');

beforeEach(fn () => $this->asStaff(admin: true));

it('can render staff create page', function () {
    $this->get(StaffResource::getUrl('create'))
        ->assertSuccessful();
});

it('can create staff', function () {
    $staff = Staff::factory()->make();

    $staff->assignRole('staff');

    $formData = [
        'first_name' => $staff->first_name,
        'last_name' => $staff->last_name,
        'email' => 'test@example.com',
        'password' => 'password',
    ];

    Livewire::test(CreateStaff::class)
        ->fillForm($formData)
        ->call('create')
        ->assertHasNoFormErrors();

    unset($formData['password']);

    $record = Staff::where('email', $formData['email'])->first();

    expect(Hash::check('password', $record->password))->toBeTrue();

    $this->assertDatabaseHas(Staff::class, $formData);
});
