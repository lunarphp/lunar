<?php

use Filament\Actions\AttachAction;
use Filament\Actions\CreateAction;
use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\DiscountResource\RelationManagers\CollectionConditionRelationManager;
use Lunar\Admin\Filament\Resources\DiscountResource\RelationManagers\CollectionLimitationRelationManager;
use Lunar\FieldTypes\Text;
use Lunar\Models\Collection;
use Lunar\Models\CollectionGroup;
use Lunar\Models\Discount;
use Lunar\Models\Language;
use Lunar\Tests\Admin\Unit\Filament\TestCase;

uses(TestCase::class)
    ->group('resource.discount');

beforeEach(function () {
    Language::factory()->create([
        'default' => true,
    ]);

    $group = CollectionGroup::factory()->create(['name' => 'Main']);

    $clothing = Collection::factory()->create([
        'collection_group_id' => $group->getKey(),
        'attribute_data' => collect([
            'name' => new Text('Clothing'),
        ]),
    ]);

    $this->collection = Collection::factory()->create([
        'collection_group_id' => $group->getKey(),
        'attribute_data' => collect([
            'name' => new Text('Tops'),
        ]),
    ]);

    $this->collection->appendToNode($clothing)->save();

    $this->discount = Discount::factory()->create();

    $this->asStaff(admin: true);
});

it('shows the collection path against a condition', function () {
    $this->discount->discountableConditions()->create([
        'discountable_type' => $this->collection->getMorphClass(),
        'discountable_id' => $this->collection->getKey(),
    ]);

    Livewire::test(CollectionConditionRelationManager::class, [
        'ownerRecord' => $this->discount,
        'pageClass' => 'collectionConditionRelationManager',
    ])
        ->assertSuccessful()
        ->assertSee('Tops')
        ->assertSee('Main > Clothing');
});

// The create form resolves its selected option through the path label, which the
// rendered table does not exercise.
it('can add a collection as a condition', function () {
    Livewire::test(CollectionConditionRelationManager::class, [
        'ownerRecord' => $this->discount,
        'pageClass' => 'collectionConditionRelationManager',
    ])
        ->callTableAction(CreateAction::class, data: [
            'discountable_id' => $this->collection->getKey(),
        ])
        ->assertHasNoTableActionErrors();

    expect($this->discount->discountableConditions)->toHaveCount(1);
});

// The attach modal builds its options through the path label and the eager loaded
// options query, neither of which the rendered table exercises.
it('can attach a collection as a limitation', function () {
    Livewire::test(CollectionLimitationRelationManager::class, [
        'ownerRecord' => $this->discount,
        'pageClass' => 'collectionLimitationRelationManager',
    ])
        ->callTableAction(AttachAction::class, data: [
            'recordId' => $this->collection->getKey(),
            'type' => 'limitation',
        ])
        ->assertHasNoTableActionErrors();

    expect($this->discount->collections)->toHaveCount(1);
});

it('shows the collection path against a limitation', function () {
    $this->discount->collections()->attach($this->collection, ['type' => 'limitation']);

    Livewire::test(CollectionLimitationRelationManager::class, [
        'ownerRecord' => $this->discount,
        'pageClass' => 'collectionLimitationRelationManager',
    ])
        ->assertSuccessful()
        ->assertSee('Tops')
        ->assertSee('Main > Clothing');
});
