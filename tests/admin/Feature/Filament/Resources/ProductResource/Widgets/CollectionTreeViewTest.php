<?php

use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\CollectionGroupResource\Widgets\CollectionTreeView;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Models\Attribute;
use Lunar\Models\Collection;
use Lunar\Models\CollectionGroup;
use Lunar\Models\Language;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)
    ->group('resource.product.widgets');

it('can mount widget', function () {
    $group = CollectionGroup::factory()->create();

    Livewire::test(CollectionTreeView::class, [
        'record' => $group,
    ])->assertHasNoErrors();
});

it('can render collection tree', function () {
    $group = CollectionGroup::factory()->create();

    Language::factory()->create([
        'default' => true,
    ]);

    $collection = Collection::factory(1)->create([
        'collection_group_id' => $group->id,
    ]);

    Livewire::test(CollectionTreeView::class, [
        'record' => $group,
    ])->assertSet('nodes', CollectionTreeView::mapCollections(
        collect($collection)
    ))->assertHasNoErrors();
});

it('can create root collection', function () {
    $group = CollectionGroup::factory()->create();

    Attribute::factory()->create([
        'handle' => 'name',
        'type' => TranslatedText::class,
        'attribute_type' => 'collection',
    ]);

    $language = Language::factory()->create([
        'default' => true,
    ]);

    Livewire::test(CollectionTreeView::class, [
        'record' => $group,
    ])->callAction('createRootCollection', [
        'name' => [$language->code => 'Foo Bar'],
    ])->assertSet('nodes.0.name', 'Foo Bar')
        ->assertHasNoErrors();
});

it('can toggle collection children', function () {
    $group = CollectionGroup::factory()->create();

    Language::factory()->create([
        'default' => true,
    ]);

    $collection = Collection::factory()->create([
        'collection_group_id' => $group->id,
    ]);

    Collection::factory(2)->create([
        'collection_group_id' => $group->id,
    ])->each(
        fn ($child) => $collection->prependNode($child)
    );

    Livewire::test(CollectionTreeView::class, [
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
    $group = CollectionGroup::factory()->create();

    Attribute::factory()->create([
        'handle' => 'name',
        'type' => TranslatedText::class,
        'attribute_type' => 'collection',
    ]);

    $language = Language::factory()->create([
        'default' => true,
    ]);

    $collection = Collection::factory()->create([
        'collection_group_id' => $group->id,
    ]);

    $child = Collection::factory()->create([
        'collection_group_id' => $group->id,
    ]);

    $collection->prependNode($child);

    Livewire::test(CollectionTreeView::class, [
        'record' => $group,
    ])->callAction('addChildCollection', [
        'name' => [$language->code => 'Sub Collection'],
    ], ['id' => $collection->id])
        ->assertCount('nodes', 1)
        ->assertSet('nodes.0.children.0.id', $child->id)
        ->callAction('makeRoot', arguments: ['id' => $child->id])
        ->assertCount('nodes.0.children', 0)
        ->assertCount('nodes', 2);
});

it('can set child collection as root', function () {
    $group = CollectionGroup::factory()->create();

    Attribute::factory()->create([
        'handle' => 'name',
        'type' => TranslatedText::class,
        'attribute_type' => 'collection',
    ]);

    $language = Language::factory()->create([
        'default' => true,
    ]);

    $collection = Collection::factory()->create([
        'collection_group_id' => $group->id,
    ]);

    Livewire::test(CollectionTreeView::class, [
        'record' => $group,
    ])->callAction('addChildCollection', [
        'name' => [$language->code => 'Sub Collection'],
    ], ['id' => $collection->id])
        ->assertSet('nodes.0.children.0.name', 'Sub Collection');
});

it('can reorder collections', function () {
    $group = CollectionGroup::factory()->create();

    Language::factory()->create([
        'default' => true,
    ]);

    $collectionA = Collection::factory()->create([
        'collection_group_id' => $group->id,
    ]);

    $collectionB = Collection::factory()->create([
        'collection_group_id' => $group->id,
    ]);

    Livewire::test(CollectionTreeView::class, [
        'record' => $group,
    ])->assertSet('nodes.0.id', $collectionA->id)
        ->assertSet('nodes.1.id', $collectionB->id)
        ->call('sort', $collectionA->id, $collectionB->id, 'after')
        ->assertSet('nodes.0.id', $collectionB->id)
        ->assertSet('nodes.1.id', $collectionA->id);
});
