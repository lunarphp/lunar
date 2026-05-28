<?php

use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\CollectionResource\Pages\EditCollection;
use Lunar\Core\FieldTypes\Text;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\AttributeGroup;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\Language;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)
    ->group('resource.collection');

it('can save attributes', function () {
    Language::factory()->create([
        'default' => true,
    ]);

    $record = Collection::factory()->create();

    $group = AttributeGroup::factory()->create([
        'name' => 'Collection Details',
        'handle' => 'collection_details',
        'position' => 1,
    ]);

    Attribute::factory()->modelType('collection')->create([
        'attribute_group_id' => $group->id,
        'position' => 1,
        'name' => 'Name',
        'handle' => 'name',
        'required' => false,
        'system' => false,
        'searchable' => false,
    ]);

    $this->asStaff(admin: true);

    Livewire::test(EditCollection::class, [
        'record' => $record->getRouteKey(),
        'pageClass' => 'collectionEdit',
    ])->fillForm([
        'attribute_data' => [
            'name' => new Text('New Collection Name'),
        ],
    ])->call('save');

    expect($record->refresh()->attr('name'))->toBe('New Collection Name');
});

it('persists the dedicated name and description columns', function () {
    $language = Language::factory()->create([
        'default' => true,
        'code' => 'en',
    ]);

    $record = Collection::factory()->create();

    $this->asStaff(admin: true);

    Livewire::test(EditCollection::class, [
        'record' => $record->getRouteKey(),
        'pageClass' => 'collectionEdit',
    ])->fillForm([
        'name' => [$language->code => 'Outerwear'],
        'short_description' => [$language->code => 'Coats and jackets'],
        'description' => [$language->code => 'All of our outerwear'],
    ])->call('save')->assertHasNoFormErrors();

    $record->refresh();

    expect($record->translate('name'))->toBe('Outerwear');
    expect($record->translate('short_description'))->toBe('Coats and jackets');
    expect($record->translate('description'))->toBe('All of our outerwear');
});
