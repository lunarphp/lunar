<?php

use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\CollectionResource;
use Lunar\Admin\Filament\Resources\CollectionResource\Pages\ManageCollectionChildren;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\Language;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)
    ->group('resource.collection');

it('can render the collection children page', function () {
    Language::factory()->create([
        'default' => true,
    ]);

    $record = Collection::factory()->create();

    $this->asStaff(admin: true)
        ->get(CollectionResource::getUrl('children', [
            'record' => $record,
        ]))
        ->assertSuccessful();
});

it('can create child categories', function () {
    $language = Language::factory()->create([
        'default' => true,
    ]);

    $record = Collection::factory()->create();

    $this->asStaff();

    expect($record->children()->count())->toBe(0);

    Livewire::test(ManageCollectionChildren::class, [
        'record' => $record->getKey(),
    ])->callTableAction('createChildCollection', data: [
        'name' => [$language->code => 'Test Child Category'],
    ])->assertHasNoErrors();

    expect($record->children()->count())->toBe(1);
})->group('thisone');
