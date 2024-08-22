<?php

use Lunar\Admin\Filament\Resources\AttributeGroupResource\Pages\EditAttributeGroup;
use Lunar\Admin\Filament\Resources\AttributeGroupResource\RelationManagers\AttributesRelationManager;
use Lunar\Admin\Filament\Resources\CustomerResource\Pages\EditCustomer;
use Lunar\Admin\Filament\Resources\CustomerResource\RelationManagers\AddressRelationManager;
use Lunar\Admin\Filament\Resources\DiscountResource\Pages\EditDiscount;
use Lunar\Admin\Filament\Resources\DiscountResource\RelationManagers\ProductLimitationRelationManager;
use Lunar\Admin\Filament\Resources\ProductOptionResource\Pages\EditProductOption;
use Lunar\Admin\Filament\Resources\ProductOptionResource\RelationManagers\ValuesRelationManager;
use Lunar\Admin\Support\Facades\LunarPanel;

uses(\Lunar\Tests\Admin\Feature\Filament\TestCase::class)
    ->group('extending');

it('can extend table columns', function ($relationManager, $page) {
    $class = new class extends \Lunar\Admin\Support\Extending\RelationManagerExtension
    {
        public function extendTable(Filament\Tables\Table $table): Filament\Tables\Table
        {
            return $table->columns([
                ...$table->getColumns(),
                \Filament\Tables\Columns\TextColumn::make('test_column'),
            ]);
        }
    };

    LunarPanel::extensions([
        $relationManager => $class::class,
    ]);

    $model = $page::getResource()::getModel()::factory()->create();

    \Livewire\Livewire::test($relationManager, [
        'ownerRecord' => $model,
        'pageClass' => $page
    ])->assertTableColumnExists('test_column');
})->with([
    'AttributesRelationManager' => [AttributesRelationManager::class, EditAttributeGroup::class],
    'AddressRelationManager' => [AddressRelationManager::class, EditCustomer::class],
    'ProductLimitationRelationManager' => [ProductLimitationRelationManager::class, EditDiscount::class],
    'ValuesRelationManager' => [ValuesRelationManager::class, EditProductOption::class],
]);

it('can extend form schema', function ($relationManager, $page) {
    $class = new class extends \Lunar\Admin\Support\Extending\RelationManagerExtension
    {
        public function extendForm(Filament\Forms\Form $form): Filament\Forms\Form
        {
            $form->schema([
                ...$form->getComponents(true),
                \Filament\Forms\Components\TextInput::make('test_form_field'),
            ]);

            return $form;
        }
    };

    LunarPanel::extensions([
        $relationManager => $class::class,
    ]);

    $model = $page::getResource()::getModel()::factory()->create();

    \Livewire\Livewire::test($relationManager, [
        'ownerRecord' => $model,
        'pageClass' => $page
    ])->assertFormFieldExists('test_form_field');
})->with([
    'AttributesRelationManager' => [AttributesRelationManager::class, EditAttributeGroup::class],
    'ValuesRelationManager' => [ValuesRelationManager::class, EditProductOption::class],
]);
