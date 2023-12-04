<?php

use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\AttributeGroupResource;
use Lunar\Admin\Filament\Resources\AttributeGroupResource\Pages\EditAttributeGroup;
use Lunar\Models\AttributeGroup;

uses(\Lunar\Admin\Tests\Feature\Filament\TestCase::class)
    ->group('resource.attribute-group');

it('can render attribute group edit page', function () {
    $this->asStaff(admin: true)
        ->get(AttributeGroupResource::getUrl('edit', ['record' => AttributeGroup::factory()->create()]))
        ->assertSuccessful();
});

it('can retrieve attribute group data', function () {
    $this->asStaff();

    $attributeGroup = AttributeGroup::factory()->create();

    Livewire::test(EditAttributeGroup::class, [
        'record' => $attributeGroup->getRouteKey(),
    ])
        ->assertFormSet([
            'name.en' => $attributeGroup->translate('name'), // TODO: Needs to be translated
        ]);
});
