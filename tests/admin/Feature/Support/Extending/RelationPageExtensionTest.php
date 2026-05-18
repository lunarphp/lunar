<?php

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\CollectionResource\Pages\ManageCollectionProducts;
use Lunar\Admin\Filament\Resources\ProductResource\Pages\ManageProductMedia;
use Lunar\Admin\Support\Extending\RelationPageExtension;
use Lunar\Admin\Support\Facades\LunarPanel;
use Lunar\Models\Collection;
use Lunar\Models\Language;
use Lunar\Models\Product;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)
    ->group('extending');

it('keeps the table method public on concrete relation pages', function () {
    $method = new ReflectionMethod(ManageCollectionProducts::class, 'table');

    expect($method->isPublic())->toBeTrue()
        ->and($method->getDeclaringClass()->getName())->toBe(ManageCollectionProducts::class);
});

it('keeps the default relation page table intact when no extension is registered', function () {
    Language::factory()->create([
        'default' => true,
    ]);

    $collection = Collection::factory()->create();

    $this->asStaff(admin: true);

    Livewire::test(ManageCollectionProducts::class, [
        'record' => $collection->getRouteKey(),
    ])->assertTableColumnExists('attribute_data.name');
});

it('can extend relation page table columns', function () {
    $class = new class extends RelationPageExtension
    {
        public function extendTable(Table $table): Table
        {
            return $table->columns([
                ...$table->getColumns(),
                TextColumn::make('test_column'),
            ]);
        }
    };

    Language::factory()->create([
        'default' => true,
    ]);

    $collection = Collection::factory()->create();

    LunarPanel::extensions([
        ManageCollectionProducts::class => $class::class,
    ]);

    $this->asStaff(admin: true);

    Livewire::test(ManageCollectionProducts::class, [
        'record' => $collection->getRouteKey(),
    ])->assertTableColumnExists('test_column');
});

it('can customise page headings', function () {
    $class = new class extends RelationPageExtension
    {
        public function heading($title, Model $record): string
        {
            return 'New Heading';
        }

        public function subheading($title, Model $record): ?string
        {
            return 'New Subheading';
        }
    };

    Language::factory()->create();
    $product = Product::factory()->create();

    LunarPanel::extensions([
        ManageProductMedia::class => $class::class,
    ]);

    $this->asStaff(admin: true);

    Livewire::test(ManageProductMedia::class, [
        'record' => $product->getRouteKey(),
    ])
        ->assertSee('New Heading')
        ->assertSee('New Subheading');
});
