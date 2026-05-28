<?php

use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\BrandResource\Pages\EditBrand;
use Lunar\Core\Enums\FieldTypeEnum;
use Lunar\Core\FieldTypes\Text;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\AttributeGroup;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Language;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)
    ->group('resource.brand');

it('can save attributes', function () {
    Language::factory()->create([
        'default' => true,
    ]);

    $record = Brand::factory()->create();

    $group = AttributeGroup::factory()->create([
        'name' => 'Details',
        'handle' => 'details',
        'position' => 1,
    ]);

    Attribute::factory()->modelType('brand')->create([
        'attribute_group_id' => $group->id,
        'position' => 1,
        'name' => 'Name',
        'handle' => 'name',
        'type' => FieldTypeEnum::Text->value,
        'required' => false,
        'system' => false,
        'searchable' => false,
    ]);

    $this->asStaff(admin: true);

    Livewire::test(EditBrand::class, [
        'record' => $record->getRouteKey(),
        'pageClass' => 'brandEdit',
    ])->fillForm([
        'attribute_data' => [
            'name' => new Text('New Brand Name'),
        ],
    ])->call('save');

    expect($record->refresh()->attr('name'))->toBe('New Brand Name');
});
