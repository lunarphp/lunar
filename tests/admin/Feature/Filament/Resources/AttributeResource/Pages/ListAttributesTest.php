<?php

use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\AttributeResource;
use Lunar\Admin\Filament\Resources\AttributeResource\Pages\ListAttributes;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\AttributeGroup;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)
    ->group('resource.attribute');

it('can render attribute index page', function () {
    $this->asStaff(admin: true)
        ->get(AttributeResource::getUrl('index'))
        ->assertSuccessful();
});

it('lists grouped and ungrouped attributes together', function () {
    $this->asStaff();

    $group = AttributeGroup::factory()->create();

    $grouped = Attribute::factory()->create([
        'attribute_group_id' => $group->id,
        'name' => 'Material',
    ]);

    $ungrouped = Attribute::factory()->create([
        'attribute_group_id' => null,
        'name' => 'Warranty',
    ]);

    Livewire::test(ListAttributes::class)
        ->assertCountTableRecords(2)
        ->assertTableColumnStateSet('name', 'Material', $grouped)
        ->assertTableColumnStateSet('name', 'Warranty', $ungrouped)
        ->assertTableColumnStateSet('group.name', $group->name, $grouped)
        ->assertTableColumnStateSet('group.name', null, $ungrouped);
});

it('can filter attributes by group', function () {
    $this->asStaff();

    $group = AttributeGroup::factory()->create();

    $grouped = Attribute::factory()->create([
        'attribute_group_id' => $group->id,
    ]);

    $ungrouped = Attribute::factory()->create([
        'attribute_group_id' => null,
    ]);

    Livewire::test(ListAttributes::class)
        ->filterTable('attribute_group_id', $group->id)
        ->assertCanSeeTableRecords([$grouped])
        ->assertCanNotSeeTableRecords([$ungrouped]);
});

it('can filter to ungrouped attributes', function () {
    $this->asStaff();

    $group = AttributeGroup::factory()->create();

    $grouped = Attribute::factory()->create([
        'attribute_group_id' => $group->id,
    ]);

    $ungrouped = Attribute::factory()->create([
        'attribute_group_id' => null,
    ]);

    Livewire::test(ListAttributes::class)
        ->filterTable('attribute_group_id', 'ungrouped')
        ->assertCanSeeTableRecords([$ungrouped])
        ->assertCanNotSeeTableRecords([$grouped]);
});
