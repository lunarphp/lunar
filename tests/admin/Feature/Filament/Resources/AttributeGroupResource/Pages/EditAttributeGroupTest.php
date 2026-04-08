<?php

use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\AttributeGroupResource;
use Lunar\Admin\Filament\Resources\AttributeGroupResource\Pages\EditAttributeGroup;
use Lunar\Models\AttributeGroup;
use Lunar\Models\Language;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)
    ->group('resource.attribute-group');

it('can render attribute group edit page', function () {

    Language::factory()->create([
        'default' => true,
    ]);

    $this->asStaff(admin: true)
        ->get(AttributeGroupResource::getUrl('edit', ['record' => AttributeGroup::factory()->create()]))
        ->assertSuccessful();
});

it('can retrieve attribute group data', function () {

    $lang = Language::factory()->create([
        'default' => true,
        'code' => 'en',
    ]);

    $this->asStaff();

    $attributeGroup = AttributeGroup::factory()->create();

    Livewire::test(EditAttributeGroup::class, [
        'record' => $attributeGroup->getRouteKey(),
    ])
        ->assertFormSet([
            'name.'.$lang->code => $attributeGroup->translate('name', $lang->code),
        ]);
});
