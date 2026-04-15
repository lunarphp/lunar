<?php

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\AttributeGroupResource\Pages\EditAttributeGroup;
use Lunar\Admin\Filament\Resources\AttributeGroupResource\RelationManagers\AttributesRelationManager;
use Lunar\Admin\Filament\Resources\CustomerResource\Pages\EditCustomer;
use Lunar\Admin\Filament\Resources\CustomerResource\RelationManagers\AddressRelationManager;
use Lunar\Admin\Filament\Resources\DiscountResource\Pages\EditDiscount;
use Lunar\Admin\Filament\Resources\DiscountResource\RelationManagers\ProductLimitationRelationManager;
use Lunar\Admin\Filament\Resources\ProductOptionResource\Pages\EditProductOption;
use Lunar\Admin\Filament\Resources\ProductOptionResource\RelationManagers\ValuesRelationManager;
use Lunar\Admin\Support\Extending\RelationManagerExtension;
use Lunar\Admin\Support\Facades\LunarPanel;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)
    ->group('extending');

it('can extend table columns', function ($relationManager, $page) {
    $class = new class extends RelationManagerExtension
    {
        public function extendTable(Table $table): Table
        {
            return $table->columns([
                ...$table->getColumns(),
                TextColumn::make('test_column'),
            ]);
        }
    };

    LunarPanel::extensions([
        $relationManager => $class::class,
    ]);

    $model = $page::getResource()::getModel()::factory()->create();

    Livewire::test($relationManager, [
        'ownerRecord' => $model,
        'pageClass' => $page,
    ])->assertTableColumnExists('test_column');
})->with([
    'AttributesRelationManager' => [AttributesRelationManager::class, EditAttributeGroup::class],
    'AddressRelationManager' => [AddressRelationManager::class, EditCustomer::class],
    'ProductLimitationRelationManager' => [ProductLimitationRelationManager::class, EditDiscount::class],
    'ValuesRelationManager' => [ValuesRelationManager::class, EditProductOption::class],
]);

it('can extend form schema', function ($relationManager, $page) {
    $class = new class extends RelationManagerExtension
    {
        public function extendForm(Schema $schema): Schema
        {
            $schema->components([
                ...$schema->getComponents(true),
                TextInput::make('test_form_field'),
            ]);

            return $schema;
        }
    };

    LunarPanel::extensions([
        $relationManager => $class::class,
    ]);

    $model = $page::getResource()::getModel()::factory()->create();

    Livewire::test($relationManager, [
        'ownerRecord' => $model,
        'pageClass' => $page,
    ])->assertFormFieldExists('test_form_field', 'form');
})->with([
    'AttributesRelationManager' => [AttributesRelationManager::class, EditAttributeGroup::class],
    'ValuesRelationManager' => [ValuesRelationManager::class, EditProductOption::class],
]);
