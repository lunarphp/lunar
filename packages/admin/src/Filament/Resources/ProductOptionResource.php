<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Table;
use Lunar\Admin\Filament\Resources\ProductOptionResource\Pages\CreateProductOption;
use Lunar\Admin\Filament\Resources\ProductOptionResource\Pages\EditProductOption;
use Lunar\Admin\Filament\Resources\ProductOptionResource\Pages\ListProductOptions;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Core\Models\ProductOption;
use Lunar\Filament\RelationManagers\ProductOption\ValuesRelationManager;
use Lunar\Filament\Schemas\ProductOption\ProductOptionForm;
use Lunar\Filament\Support\Resolver;
use Lunar\Filament\Tables\ProductOption\ProductOptionTable;

class ProductOptionResource extends BaseResource
{
    protected static ?string $permission = 'settings';

    protected static ?string $model = ProductOption::class;

    protected static ?int $navigationSort = 1;

    public static function getLabel(): string
    {
        return __('lunarpanel::productoption.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunarpanel::productoption.plural_label');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::product-options');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('lunarpanel::global.sections.settings');
    }

    public static function form(Schema $schema): Schema
    {
        return Resolver::form(ProductOptionForm::class, $schema);
    }

    public static function table(Table $table): Table
    {
        return Resolver::table(ProductOptionTable::class, $table);
    }

    protected static function getDefaultRelations(): array
    {
        return [
            ValuesRelationManager::class,
        ];
    }

    protected static function getDefaultPages(): array
    {
        return [
            'index' => ListProductOptions::route('/'),
            'create' => CreateProductOption::route('/create'),
            'edit' => EditProductOption::route('/{record}/edit'),
        ];
    }
}
