<?php

use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\AttributeGroupResource;
use Lunar\Admin\Filament\Resources\AttributeGroupResource\Pages\ListAttributeGroups;
use Lunar\Core\Models\AttributeGroup;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)
    ->group('resource.attribute-group');

it('can render attribute group index page', function () {
    $this->asStaff(admin: true)
        ->get(AttributeGroupResource::getUrl('index'))
        ->assertSuccessful();
});

it('shows translated attribute group names as a single state', function () {
    $this->asStaff();

    $attributeGroup = AttributeGroup::factory()->create([
        'name' => 'Details',
    ]);

    Livewire::test(ListAttributeGroups::class)
        ->assertCountTableRecords(1)
        ->assertTableColumnStateSet('name', 'Details', $attributeGroup);
});
