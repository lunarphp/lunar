<?php

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\ActivityResource\Pages\ListActivities;
use Lunar\Admin\Filament\Resources\AttributeGroupResource\Pages\ListAttributeGroups;
use Lunar\Admin\Filament\Resources\CurrencyResource;
use Lunar\Admin\Filament\Resources\CurrencyResource\Pages\EditCurrency;
use Lunar\Admin\Filament\Resources\CurrencyResource\Pages\ListCurrencies;
use Lunar\Admin\Filament\Resources\CustomerResource;
use Lunar\Admin\Filament\Resources\LanguageResource;
use Lunar\Admin\Filament\Resources\LanguageResource\Pages\EditLanguage;
use Lunar\Admin\Filament\Resources\LanguageResource\Pages\ListLanguages;
use Lunar\Admin\Support\Extending\ResourceExtension;
use Lunar\Admin\Support\Facades\LunarPanel;
use Lunar\Filament\Schemas\Currency\CurrencyForm;
use Lunar\Filament\Schemas\Language\LanguageForm;
use Lunar\Filament\Tables\Activity\ActivityTable;
use Lunar\Filament\Tables\AttributeGroup\AttributeGroupTable;
use Lunar\Filament\Tables\Currency\CurrencyTable;
use Lunar\Filament\Tables\Language\LanguageTable;
use Lunar\Tests\Admin\Feature\Filament\TestCase;
use Lunar\Tests\Admin\Stubs\Filament\TestCustomerAddressRelationManager;

uses(TestCase::class)
    ->group('extending', 'extending.resources');

it('can extend relationship managers via getRelations hook on the resource', function () {
    $class = new class extends ResourceExtension
    {
        public function getRelations(array $managers): array
        {
            return [
                TestCustomerAddressRelationManager::class,
            ];
        }
    };

    LunarPanel::extensions([
        CustomerResource::class => $class::class,
    ]);

    $relations = CustomerResource::getRelations();
    expect($relations)->toContain(TestCustomerAddressRelationManager::class);
});

it('can extend table columns via configureTable hook on the split-class table', function ($table, $page) {
    $class = new class extends ResourceExtension
    {
        public function configureTable(Table $table): Table
        {
            return $table->columns([
                ...$table->getColumns(),
                TextColumn::make('test_column'),
            ]);
        }
    };

    LunarPanel::extensions([
        $table => $class::class,
    ]);

    $this->asStaff();

    Livewire::test($page)->assertTableColumnExists('test_column');
})->with([
    'CurrencyTable' => [CurrencyTable::class, ListCurrencies::class],
    'LanguageTable' => [LanguageTable::class, ListLanguages::class],
    'ActivityTable' => [ActivityTable::class, ListActivities::class],
    'AttributeGroupTable' => [AttributeGroupTable::class, ListAttributeGroups::class],
]);

it('can extend form schema via configureForm hook on the split-class form', function ($resource, $form, $page) {
    $class = new class extends ResourceExtension
    {
        public function configureForm(Schema $schema): Schema
        {
            $schema->components([
                ...$schema->getComponents(true),
                TextInput::make('test_form_field'),
            ]);

            return $schema;
        }
    };

    LunarPanel::extensions([
        $form => $class::class,
    ]);

    $this->asStaff(admin: true);

    $model = $resource::getModel()::factory()->create();

    Livewire::test($page, [
        'record' => $model->getRouteKey(),
    ])->assertFormFieldExists('test_form_field');
})->with([
    'CurrencyForm' => [CurrencyResource::class, CurrencyForm::class, EditCurrency::class],
    'LanguageForm' => [LanguageResource::class, LanguageForm::class, EditLanguage::class],
]);
