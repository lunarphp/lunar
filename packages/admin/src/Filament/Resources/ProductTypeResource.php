<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Lunar\Admin\Filament\Resources\ProductTypeResource\Pages\CreateProductType;
use Lunar\Admin\Filament\Resources\ProductTypeResource\Pages\EditProductType;
use Lunar\Admin\Filament\Resources\ProductTypeResource\Pages\ListProductTypes;
use Lunar\Admin\Support\Forms\Components\AttributeSelector;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Models\Contracts\ProductType as ProductTypeContract;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;

class ProductTypeResource extends BaseResource
{
    protected static ?string $permission = 'catalog:manage-products';

    protected static ?string $model = ProductTypeContract::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-swatch';  // TODO: remove me in Filament 3.1

    protected static ?int $navigationSort = 2;

    public static function getLabel(): string
    {
        return __('lunarpanel::producttype.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunarpanel::producttype.plural_label');
    }

    public static function getNavigationParentItem(): ?string
    {
        return __('lunarpanel::product.plural_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('lunarpanel::global.sections.catalog');
    }

    public static function getDefaultForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()->schema(
                    static::getMainFormComponents()
                ),
                Tabs::make('Attributes')->tabs([
                    Tab::make(__('lunarpanel::producttype.tabs.product_attributes.label'))
                        ->schema([
                            AttributeSelector::make('mappedAttributes')
                                ->withType(Product::morphName())
                                ->relationship(name: 'mappedAttributes')
                                ->label('')
                                ->columnSpan(2),
                        ]),
                    Tab::make(__('lunarpanel::producttype.tabs.variant_attributes.label'))
                        ->schema([
                            AttributeSelector::make('mappedAttributes')
                                ->withType(ProductVariant::morphName())
                                ->relationship(name: 'mappedAttributes')
                                ->label('')
                                ->columnSpan(2),
                        ])->visible(
                            config('lunar.panel.enable_variants', true)
                        ),

                ])->columnSpan(2),
            ]);
    }

    protected static function getMainFormComponents(): array
    {
        return [
            static::getNameFormComponent(),
        ];
    }

    protected static function getNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('lunarpanel::producttype.form.name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    public static function getDefaultTable(Table $table): Table
    {
        return $table
            ->columns(static::getTableColumns())
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected static function getTableColumns(): array
    {
        return [
            TextColumn::make('name')
                ->label(__('lunarpanel::producttype.table.name.label')),
            TextColumn::make('products_count')
                ->counts('products')
                ->formatStateUsing(
                    fn ($state) => number_format($state, 0)
                )
                ->label(__('lunarpanel::producttype.table.products_count.label')),
            TextColumn::make('product_attributes_count')
                ->counts('productAttributes')
                ->formatStateUsing(
                    fn ($state) => number_format($state, 0)
                )
                ->label(__('lunarpanel::producttype.table.product_attributes_count.label')),
            TextColumn::make('variant_attributes_count')
                ->counts('variantAttributes')
                ->formatStateUsing(
                    fn ($state) => number_format($state, 0)
                )
                ->label(__('lunarpanel::producttype.table.variant_attributes_count.label'))
                ->visible(
                    config('lunar.panel.enable_variants', true)
                ),
        ];
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getDefaultPages(): array
    {
        return [
            'index' => ListProductTypes::route('/'),
            'create' => CreateProductType::route('/create'),
            'edit' => EditProductType::route('/{record}/edit'),
        ];
    }
}
