<?php

use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\ProductTypeResource;
use Lunar\Models\ProductType;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)
    ->group('panel');

/**
 * Livewire skips Laravel's TrimStrings middleware, so the panel registers
 * Filament's trim() globally for free-text fields instead.
 */
it('configures free-text form fields to trim input', function () {
    expect(TextInput::make('test')->isTrimmed())->toBeTrue()
        ->and(Textarea::make('test')->isTrimmed())->toBeTrue()
        ->and(TagsInput::make('test')->isTrimmed())->toBeTrue();
});

it('trims whitespace from form input before saving', function () {
    Livewire::actingAs($this->makeStaff(admin: true), 'staff')
        ->test(ProductTypeResource\Pages\CreateProductType::class)
        ->fillForm([
            'name' => '  Padded Name  ',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(ProductType::class, [
        'name' => 'Padded Name',
    ]);
});
