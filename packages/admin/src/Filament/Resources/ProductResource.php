<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Resources\ProductResource\Pages;
use Lunar\Admin\Filament\Resources\ProductResource\RelationManagers\CustomerGroupRelationManager;
use Lunar\Admin\Support\Forms\Components\Attributes;
use Lunar\Admin\Support\RelationManagers\ChannelRelationManager;
use Lunar\Admin\Support\RelationManagers\MediaRelationManager;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Admin\Support\Forms\Components\Tags;
use Lunar\Models\Currency;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;

class ProductResource extends BaseResource
{
    protected static ?string $permission = 'catalog:manage-products';

    protected static ?string $model = Product::class;

    protected static ?string $recordTitleAttribute = 'record_title';

    protected static ?int $navigationSort = 1;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::End;

    public static function getLabel(): string
    {
        return __('lunarpanel::product.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunarpanel::product.plural_label');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::products');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('lunarpanel::global.sections.catalog');
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            Pages\EditProduct::class,
            Pages\ManageProductAvailability::class,
            Pages\ManageProductMedia::class,
            Pages\ManageProductPricing::class,
            Pages\ManageProductIdentifiers::class,
            Pages\ManageProductInventory::class,
            Pages\ManageProductShipping::class,
            Pages\ManageProductVariants::class,
            Pages\ManageProductUrls::class,
            Pages\ManageProductCollections::class,
            Pages\ManageProductAssociations::class,
        ]);
    }

    public static function getDefaultForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema(
                        static::getMainFormComponents(),
                    ),
                static::getAttributeDataFormComponent(),
            ])
            ->columns(1);
    }

    protected static function getMainFormComponents(): array
    {
        return [
            static::getBrandFormComponent(),
            static::getProductTypeFormComponent(),
            static::getStatusFormComponent(),
            static::getTagsFormComponent(),
        ];
    }

    public static function getSkuValidation(): array
    {
        return static::callLunarHook('extendSkuValidation', [
            'required' => true,
            'unique' => true,
        ]);
    }

    public static function getSkuFormComponent(): Component
    {
        $validation = static::getSkuValidation();

        $input = Forms\Components\TextInput::make('sku')
            ->label(__('lunarpanel::product.form.sku.label'))
            ->required($validation['required'] ?? false);

        if ($validation['unique'] ?? false) {
            $input->unique(function () {
                return (new ProductVariant)->getTable();
            });
        }

        return $input;
    }

    public static function getBasePriceFormComponent(): Component
    {
        $currency = Currency::getDefault();

        return Forms\Components\TextInput::make('base_price')->numeric()->prefix(
            $currency->code
        )->rules([
            'min:1',
            "decimal:0,{$currency->decimal_places}",
        ])->required();
    }

    public static function getBaseNameFormComponent(): Component
    {
        return Forms\Components\TextInput::make('name')
            ->label(__('lunarpanel::product.form.name.label'))->required();
    }

    protected static function getBrandFormComponent(): Component
    {
        return Forms\Components\Select::make('brand_id')
            ->label(__('lunarpanel::product.form.brand.label'))
            ->relationship('brand', 'name')
            ->searchable()
            ->preload()
            ->createOptionForm([
                Forms\Components\TextInput::make('name')
                    ->required(),
            ]);
    }

    public static function getProductTypeFormComponent(): Component
    {
        return Forms\Components\Select::make('product_type_id')
            ->label(__('lunarpanel::product.form.producttype.label'))
            ->relationship('productType', 'name')
            ->searchable()
            ->preload()
            ->live()
            ->required();
    }

    protected static function getStatusFormComponent(): Component
    {
        return Forms\Components\Select::make('status')
            ->label(__('lunarpanel::product.form.status.label'))
            ->options([
                'draft' => 'Draft',
                'published' => 'Published',
            ])
            ->selectablePlaceholder(false);
    }

    protected static function getTagsFormComponent(): Component
    {
        return Tags::make('tags')
            ->label(__('lunarpanel::product.form.tags.label'));
    }

    protected static function getAttributeDataFormComponent(): Component
    {
        return Attributes::make()->statePath('attribute_data');
    }

    public static function getDefaultTable(Table $table): Table
    {
        return $table
            ->columns(static::getTableColumns())
            ->filters([
                Tables\Filters\SelectFilter::make('brand')
                    ->relationship('brand', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->selectCurrentPageOnly()
            ->deferLoading();
    }

    protected static function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('status')
                ->label(__('lunarpanel::product.table.status.label'))
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'draft' => 'warning',
                    'published' => 'success',
                }),
            SpatieMediaLibraryImageColumn::make('thumbnail')
                ->collection('images')
                ->conversion('small')
                ->limit(1)
                ->square()
                ->label(''),
            Tables\Columns\TextColumn::make('attribute_data.name')
                ->formatStateUsing(fn (Model $record): string => $record->translateAttribute('name'))
                ->limit(50)
                ->tooltip(function (Tables\Columns\TextColumn $column, Model $record): ?string {
                    $state = $column->getState();

                    if (strlen($record->translateAttribute('name')) <= $column->getCharacterLimit()) {
                        return null;
                    }

                    // Only render the tooltip if the column contents exceeds the length limit.
                    return $record->translateAttribute('name');
                })
                ->label(__('lunarpanel::product.table.name.label')),
            Tables\Columns\TextColumn::make('brand.name')
                ->label(__('lunarpanel::product.table.brand.label'))
                ->toggleable()
                ->searchable(),
            Tables\Columns\TextColumn::make('variants.sku')
                ->label(__('lunarpanel::product.table.sku.label'))
                ->toggleable(),
            Tables\Columns\TextColumn::make('variants_sum_stock')
                ->label(__('lunarpanel::product.table.stock.label'))
                ->sum('variants', 'stock'),
            Tables\Columns\TextColumn::make('productType.name')
                ->label(__('lunarpanel::product.table.producttype.label'))
                ->limit(30)
                ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                    $state = $column->getState();

                    if (strlen($state) <= $column->getCharacterLimit()) {
                        return null;
                    }

                    // Only render the tooltip if the column contents exceeds the length limit.
                    return $state;
                })
                ->toggleable(),
        ];
    }

    public static function getDefaultRelations(): array
    {
        return [
            RelationGroup::make('Availability', [
                ChannelRelationManager::class,
                CustomerGroupRelationManager::class,
            ]),
            MediaRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
            'availability' => Pages\ManageProductAvailability::route('/{record}/availability'),
            'identifiers' => Pages\ManageProductIdentifiers::route('/{record}/identifiers'),
            'media' => Pages\ManageProductMedia::route('/{record}/media'),
            'pricing' => Pages\ManageProductPricing::route('/{record}/pricing'),
            'inventory' => Pages\ManageProductInventory::route('/{record}/inventory'),
            'shipping' => Pages\ManageProductShipping::route('/{record}/shipping'),
            'variants' => Pages\ManageProductVariants::route('/{record}/variants'),
            'urls' => Pages\ManageProductUrls::route('/{record}/urls'),
            'collections' => Pages\ManageProductCollections::route('/{record}/collections'),
            'associations' => Pages\ManageProductAssociations::route('/{record}/associations'),
        ];
    }
}
