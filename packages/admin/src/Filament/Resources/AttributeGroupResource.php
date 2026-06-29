<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Table;
use Lunar\Admin\Filament\Resources\AttributeGroupResource\Pages\CreateAttributeGroup;
use Lunar\Admin\Filament\Resources\AttributeGroupResource\Pages\EditAttributeGroup;
use Lunar\Admin\Filament\Resources\AttributeGroupResource\Pages\ListAttributeGroups;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Core\Models\AttributeGroup;
use Lunar\Filament\RelationManagers\AttributeGroup\AttributesRelationManager;
use Lunar\Filament\Schemas\AttributeGroup\AttributeGroupForm;
use Lunar\Filament\Support\Resolver;
use Lunar\Filament\Tables\AttributeGroup\AttributeGroupTable;

class AttributeGroupResource extends BaseResource
{
    protected static ?string $permission = 'settings:manage-attributes';

    protected static ?string $model = AttributeGroup::class;

    protected static ?int $navigationSort = 1;

    public static function getLabel(): string
    {
        return __('lunarpanel::attributegroup.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunarpanel::attributegroup.plural_label');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::attributes');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('lunarpanel::global.sections.settings');
    }

    public static function form(Schema $schema): Schema
    {
        return Resolver::form(AttributeGroupForm::class, $schema);
    }

    public static function table(Table $table): Table
    {
        return Resolver::table(AttributeGroupTable::class, $table);
    }

    protected static function getDefaultRelations(): array
    {
        return [
            AttributesRelationManager::class,
        ];
    }

    protected static function getDefaultPages(): array
    {
        return [
            'index' => ListAttributeGroups::route('/'),
            'create' => CreateAttributeGroup::route('/create'),
            'edit' => EditAttributeGroup::route('/{record}/edit'),
        ];
    }
}
