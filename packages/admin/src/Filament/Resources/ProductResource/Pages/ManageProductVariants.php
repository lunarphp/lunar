<?php

namespace Lunar\Admin\Filament\Resources\ProductResource\Pages;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Table;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Filament\Resources\ProductResource\Widgets\ProductOptionsWidget;
use Lunar\Admin\Support\Pages\BaseManageRelatedRecords;

class ManageProductVariants extends BaseManageRelatedRecords
{
    protected static string $resource = ProductResource::class;

    protected static string $relationship = 'variants';

    protected function getDefaultHeaderWidgets(): array
    {
        return [
            ProductOptionsWidget::class,
        ];
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::product-variants');
    }

    public static function shouldRegisterNavigation(array $parameters = []): bool
    {
        return config('lunar.panel.enable_variants', true);
    }

    public static function canAccess(array $parameters = []): bool
    {
        if (! config('lunar.panel.enable_variants', true)) {
            return false;
        }

        return parent::canAccess($parameters);
    }

    public function getTitle(): string
    {
        return __('lunarpanel::product.pages.variants.label');
    }

    public static function getNavigationLabel(): string
    {
        return __('lunarpanel::product.pages.variants.label');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return parent::table($table);
    }

    protected function getDefaultTable(Table $table): Table
    {
        return $table;
    }
}
