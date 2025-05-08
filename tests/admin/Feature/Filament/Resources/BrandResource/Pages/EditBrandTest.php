<?php

uses(\Lunar\Tests\Admin\Feature\Filament\TestCase::class)
    ->group('resource.brand');

it('can save attributes', function () {
    \Lunar\Models\Language::factory()->create([
        'default' => true,
    ]);

    $record = \Lunar\Models\Brand::factory()->create();

    $group = \Lunar\Models\AttributeGroup::factory()->create([
        'attributable_type' => 'brand',
        'name' => [
            'en' => 'Details',
        ],
        'handle' => 'details',
        'position' => 1,
    ]);

    $attribute = \Lunar\Models\Attribute::factory()->create([
        'attribute_type' => 'brand',
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
        'attributable_type' => 'brand',
        'attributable_id' => $record->id,
    ]);

    $this->asStaff(admin: true);

    \Livewire\Livewire::test(\Lunar\Admin\Filament\Resources\BrandResource\Pages\EditBrand::class, [
        'record' => $record->getRouteKey(),
        'pageClass' => 'brandEdit',
    ])->fillForm([
        'attribute_data' => [
            'name' => new \Lunar\FieldTypes\Text('New Brand Name'),
        ],
    ])->call('save');

    expect($record->refresh()->attr('name'))->toBe('New Brand Name');
});
