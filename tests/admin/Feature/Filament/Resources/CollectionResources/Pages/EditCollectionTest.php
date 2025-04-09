<?php

uses(\Lunar\Tests\Admin\Feature\Filament\TestCase::class)
    ->group('resource.collection');

it('can save attributes', function () {
    \Lunar\Models\Language::factory()->create([
        'default' => true,
    ]);

    $record = \Lunar\Models\Collection::factory()->create();

    $group = \Lunar\Models\AttributeGroup::factory()->create([
        'attributable_type' => 'collection',
        'name' => [
            'en' => 'Collection Details',
        ],
        'handle' => 'collection_details',
        'position' => 1,
    ]);

    $attribute = \Lunar\Models\Attribute::factory()->create([
        'attribute_type' => 'collection',
        'attribute_group_id' => $group->id,
        'position' => 1,
        'name' => [
            'en' => 'Name',
        ],
        'handle' => 'name',
        'section' => 'main',
        'required' => false,
        'system' => false,
        'searchable' => false,
    ]);

    \Illuminate\Support\Facades\DB::table('lunar_attributables')->insert([
        'attribute_id' => $attribute->id,
        'attributable_type' => 'product_type',
        'attributable_id' => $record->id,
    ]);

    $this->asStaff(admin: true);

    \Livewire\Livewire::test(\Lunar\Admin\Filament\Resources\CollectionResource\Pages\EditCollection::class, [
        'record' => $record->getRouteKey(),
        'pageClass' => 'collectionEdit',
    ])->fillForm([
        'attribute_data' => [
            'name' => new \Lunar\FieldTypes\Text('New Collection Name'),
        ],
    ])->call('save');

    expect($record->refresh()->attr('name'))->toBe('New Collection Name');
});
