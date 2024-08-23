<?php

use Lunar\Admin\Filament\Resources\CollectionResource\Pages\ManageCollectionChildren;

uses(\Lunar\Tests\Admin\Feature\Filament\TestCase::class)
    ->group('resource.collection');

it('can render the collection children page', function () {
    \Lunar\Models\Language::factory()->create([
        'default' => true,
    ]);

    $record = \Lunar\Models\Collection::factory()->create();

    $this->asStaff(admin: true)
        ->get(\Lunar\Admin\Filament\Resources\CollectionResource::getUrl('children', [
            'record' => $record,
        ]))
        ->assertSuccessful();
});

it('can create child categories', function () {
    $language = \Lunar\Models\Language::factory()->create([
        'default' => true,
    ]);

    $record = \Lunar\Models\Collection::factory()->create();

    \Lunar\Models\Attribute::factory()->create([
        'name' => [
            'en' => 'Name',
        ],
        'description' => [
            'en' => 'Description',
        ],
        'handle' => 'name',
        'type' => \Lunar\FieldTypes\TranslatedText::class,
        'attribute_type' => 'collection',
    ]);

    $this->asStaff();

    expect($record->children()->count())->toBe(0);

    \Livewire\Livewire::test(ManageCollectionChildren::class, [
        'record' => $record->getKey(),
    ])->callTableAction('createChildCollection', data: [
        'name' => [$language->code => 'Test Child Category'],
    ])->assertHasNoErrors();

    expect($record->children()->count())->toBe(1);
})->group('thisone');
