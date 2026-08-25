<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Table;
use Lunar\Admin\Filament\Resources\AttributeResource\Pages\CreateAttribute;
use Lunar\Admin\Filament\Resources\AttributeResource\Pages\EditAttribute;
use Lunar\Admin\Filament\Resources\AttributeResource\Pages\ListAttributes;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Core\Models\Attribute;
use Lunar\Filament\Schemas\Attribute\AttributeForm;
use Lunar\Filament\Support\Resolver;
use Lunar\Filament\Tables\Attribute\AttributeTable;

/**
 * Standalone attributes surface (spec 0063). Reaches every attribute,
 * grouped or not — the attribute group relation manager only ever sees a
 * group's own attributes.
 */
class AttributeResource extends BaseResource
{
    protected static ?string $permission = 'settings:manage-attributes';

    protected static ?string $model = Attribute::class;

    protected static ?int $navigationSort = 1;

    public static function getLabel(): string
    {
        return __('lunarpanel::attribute.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunarpanel::attribute.plural_label');
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
        return Resolver::form(AttributeForm::class, $schema);
    }

    public static function table(Table $table): Table
    {
        return Resolver::table(AttributeTable::class, $table);
    }

    protected static function getDefaultPages(): array
    {
        return [
            'index' => ListAttributes::route('/'),
            'create' => CreateAttribute::route('/create'),
            'edit' => EditAttribute::route('/{record}/edit'),
        ];
    }
}
