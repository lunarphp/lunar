<?php

use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\CollectionResource\Pages\EditCollection;
use Lunar\FieldTypes\Text;
use Lunar\Models\Attribute;
use Lunar\Models\AttributeGroup;
use Lunar\Models\Collection;
use Lunar\Models\Language;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)
    ->group('resource.collection');

it('can save attributes', function () {
    Language::factory()->create([
        'default' => true,
    ]);

    $record = Collection::factory()->create();

    $group = AttributeGroup::factory()->create([
        'attributable_type' => 'collection',
        'name' => [
            'en' => 'Collection Details',
        ],
        'handle' => 'collection_details',
        'position' => 1,
    ]);

    $attribute = Attribute::factory()->create([
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

    DB::table('lunar_attributables')->insert([
        'attribute_id' => $attribute->id,
        'attributable_type' => 'product_type',
        'attributable_id' => $record->id,
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
