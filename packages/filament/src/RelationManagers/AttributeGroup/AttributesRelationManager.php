<?php

namespace Lunar\Filament\RelationManagers\AttributeGroup;

use Filament\Actions\BulkActionGroup;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Filament\Actions\Attributes\CreateAttributeAction;
use Lunar\Filament\Actions\Attributes\DeleteAttributeAction;
use Lunar\Filament\Actions\Attributes\DeleteAttributesBulkAction;
use Lunar\Filament\Actions\Attributes\EditAttributeAction;
use Lunar\Filament\RelationManagers\BaseRelationManager;
use Lunar\Filament\Schemas\Attribute\AttributeForm;
use Lunar\Filament\Support\Resolver;
use Lunar\Filament\Tables\Attribute\AttributeTable;

/**
 * Manages a group's attributes from the attribute group edit page. Delegates
 * to the shared AttributeForm / AttributeTable and attribute actions (spec
 * 0063), omitting the group select and column — the owner record supplies
 * the group.
 */
class AttributesRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'attributes';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('lunar-filament::attribute.plural_label');
    }

    protected static ?string $recordTitleAttribute = 'name';

    public function getDefaultForm(Schema $schema): Schema
    {
        $form = Resolver::resolve(AttributeForm::class);

        return $schema
            ->components([
                $form::getNameComponent(),
                $form::getHandleComponent(),
                $form::getModelTypesComponent(),
                $form::getFlagsComponent(),
                $form::getValidationRulesComponent(),
                $form::getTypeComponent(),
                $form::getConfigurationComponent(),
            ]);
    }

    public function getDefaultTable(Table $table): Table
    {
        $attributeTable = Resolver::resolve(AttributeTable::class);

        return $table
            ->columns([
                $attributeTable::getNameColumn(),
                $attributeTable::getHandleColumn(),
                $attributeTable::getTypeColumn(),
            ])
            ->headerActions([
                CreateAttributeAction::make(),
            ])
            ->recordActions([
                EditAttributeAction::make(),
                DeleteAttributeAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteAttributesBulkAction::make(),
                ]),
            ])
            ->defaultSort('position', 'asc')
            ->reorderable('position');
    }
}
