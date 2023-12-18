<?php

use Lunar\Admin\Filament\Resources\CollectionGroupResource\Widgets\CollectionTreeView;

uses(\Lunar\Admin\Tests\Feature\Filament\TestCase::class)
    ->group('resource.product.widgets');

it('can mount widget', function () {
    $group = \Lunar\Models\CollectionGroup::factory()->create();

    \Livewire\Livewire::test(CollectionTreeView::class, [
        'record' => $group,
    ])->assertHasNoErrors();
});

it('can render collection tree', function () {
    $group = \Lunar\Models\CollectionGroup::factory()->create();

    \Lunar\Models\Language::factory()->create([
        'default' => true,
    ]);

    $collection = \Lunar\Models\Collection::factory(1)->create([
        'collection_group_id' => $group->id,
    ]);

    \Livewire\Livewire::test(CollectionTreeView::class, [
        'record' => $group,
    ])->assertSet('nodes', CollectionTreeView::mapCollections(
        collect($collection)
    ))->assertHasNoErrors();
});

it('can create root collection', function () {
    $group = \Lunar\Models\CollectionGroup::factory()->create();

    \Lunar\Models\Attribute::factory()->create([
        'handle' => 'name',
        'type' => \Lunar\FieldTypes\Text::class,
        'attribute_type' => \Lunar\Models\Collection::class,
    ]);

    \Lunar\Models\Language::factory()->create([
        'default' => true,
    ]);

    \Livewire\Livewire::test(CollectionTreeView::class, [
        'record' => $group,
    ])->callAction('createRootCollection', [
        'name' => 'Foo Bar',
    ])->assertSet('nodes.0.name', 'Foo Bar')
        ->assertHasNoErrors();
});

it('can toggle collection children', function () {
    $group = \Lunar\Models\CollectionGroup::factory()->create();

    \Lunar\Models\Language::factory()->create([
        'default' => true,
    ]);

    $collection = \Lunar\Models\Collection::factory()->create([
        'collection_group_id' => $group->id,
    ]);

    \Lunar\Models\Collection::factory(2)->create([
        'collection_group_id' => $group->id,
    ])->each(
        fn ($child) => $collection->prependNode($child)
    );

    \Livewire\Livewire::test(CollectionTreeView::class, [
        'record' => $group,
    ])->assertSet('nodes.0.children', [])
        ->call('toggleChildren', $collection->id)
        ->assertSet('nodes.0.children', CollectionTreeView::mapCollections(
            $collection->children()->defaultOrder()->get()
        ))
        ->call('toggleChildren', $collection->id)
        ->assertSet('nodes.0.children', [])
        ->assertHasNoErrors();
});

it('can create child collection', function () {
    $group = \Lunar\Models\CollectionGroup::factory()->create();

    \Lunar\Models\Attribute::factory()->create([
        'handle' => 'name',
        'type' => \Lunar\FieldTypes\Text::class,
        'attribute_type' => \Lunar\Models\Collection::class,
    ]);

    \Lunar\Models\Language::factory()->create([
        'default' => true,
    ]);

    $collection = \Lunar\Models\Collection::factory()->create([
        'collection_group_id' => $group->id,
    ]);

    $child = \Lunar\Models\Collection::factory()->create([
        'collection_group_id' => $group->id,
    ]);

    $collection->prependNode($child);

    \Livewire\Livewire::test(CollectionTreeView::class, [
        'record' => $group,
    ])->callAction('addChildCollection', [
        'name' => 'Sub Collection',
    ], ['id' => $collection->id])
        ->assertCount('nodes', 1)
        ->assertSet('nodes.0.children.0.id', $child->id)
        ->mountAction('makeRoot', ['id' => $child->id])
        ->callAction('makeRoot')
        ->assertCount('nodes.0.children', 0)
        ->assertCount('nodes', 2);
});

it('can set child collection as root', function () {
    $group = \Lunar\Models\CollectionGroup::factory()->create();

    \Lunar\Models\Attribute::factory()->create([
        'handle' => 'name',
        'type' => \Lunar\FieldTypes\Text::class,
        'attribute_type' => \Lunar\Models\Collection::class,
    ]);

    \Lunar\Models\Language::factory()->create([
        'default' => true,
    ]);

    $collection = \Lunar\Models\Collection::factory()->create([
        'collection_group_id' => $group->id,
    ]);

    \Livewire\Livewire::test(CollectionTreeView::class, [
        'record' => $group,
    ])->callAction('addChildCollection', [
        'name' => 'Sub Collection',
    ], ['id' => $collection->id])
        ->assertSet('nodes.0.children.0.name', 'Sub Collection');
});

it('can reorder collections', function () {
    $group = \Lunar\Models\CollectionGroup::factory()->create();

    \Lunar\Models\Language::factory()->create([
        'default' => true,
    ]);

    $collectionA = \Lunar\Models\Collection::factory()->create([
        'collection_group_id' => $group->id,
    ]);

    $collectionB = \Lunar\Models\Collection::factory()->create([
        'collection_group_id' => $group->id,
    ]);

    \Livewire\Livewire::test(CollectionTreeView::class, [
        'record' => $group,
    ])->assertSet('nodes.0.id', $collectionA->id)
        ->assertSet('nodes.1.id', $collectionB->id)
        ->call('sort', $collectionA->id, $collectionB->id, 'after')
        ->assertSet('nodes.0.id', $collectionB->id)
        ->assertSet('nodes.1.id', $collectionA->id);
});
