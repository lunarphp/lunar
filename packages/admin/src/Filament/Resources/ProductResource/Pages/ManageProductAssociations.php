<?php

namespace Lunar\Admin\Filament\Resources\ProductResource\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Table;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Support\Pages\BaseManageRelatedRecords;
use Lunar\Models\Product;
use Lunar\Models\ProductAssociation;

class ManageProductAssociations extends BaseManageRelatedRecords
{
    protected static string $resource = ProductResource::class;

    protected static string $relationship = 'associations';

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::product-associations');
    }

    public function getTitle(): string
    {
        return __('lunarpanel::product.pages.associations.label');
    }

    public static function getNavigationLabel(): string
    {
        return __('lunarpanel::product.pages.associations.label');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_target_id')
                    ->label('Product')
                    ->required()
                    ->searchable(true)
                    ->getSearchResultsUsing(static function (Forms\Components\Select $component, string $search): array {
                        return Product::search($search)
                            ->get()
                            ->mapWithKeys(fn (Product $record): array => [$record->getKey() => $record->translateAttribute('name')])
                            ->all();
                    }),
                Forms\Components\Select::make('type')
                    ->required()
                    ->options([
                        ProductAssociation::ALTERNATE => 'Alternate',
                        ProductAssociation::CROSS_SELL => 'Cross-Sell',
                        ProductAssociation::UP_SELL => 'Upsell',
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->inverseRelationship('parent')
            ->columns([
                Tables\Columns\TextColumn::make('target')
                    ->formatStateUsing(fn (ProductAssociation $record): string => $record->target->translateAttribute('name'))
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column, ProductAssociation $record): ?string {
                        $state = $column->getState();

                        if (strlen($record->target->translateAttribute('name')) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        // Only render the tooltip if the column contents exceeds the length limit.
                        return $record->target->translateAttribute('name');
                    })
                    ->label(__('lunarpanel::product.table.name.label')),
                Tables\Columns\TextColumn::make('target.variants.sku')
                    ->label('SKU'),
                Tables\Columns\TextColumn::make('type'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
