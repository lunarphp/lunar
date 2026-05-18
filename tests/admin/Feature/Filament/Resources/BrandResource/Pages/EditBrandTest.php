<?php

use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\BrandResource\Pages\EditBrand;
use Lunar\FieldTypes\Text;
use Lunar\Models\Attribute;
use Lunar\Models\AttributeGroup;
use Lunar\Models\Brand;
use Lunar\Models\Language;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)
    ->group('resource.brand');

it('can save attributes', function () {
    Language::factory()->create([
        'default' => true,
    ]);

    $record = Brand::factory()->create();

    $group = AttributeGroup::factory()->create([
        'attributable_type' => 'brand',
        'name' => [
            'en' => 'Details',
        ],
        'handle' => 'details',
        'position' => 1,
    ]);

    $attribute = Attribute::factory()->create([
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

    DB::table('lunar_attributables')->insert([
        'attribute_id' => $attribute->id,
        'attributable_type' => 'brand',
        'attributable_id' => $record->id,
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
