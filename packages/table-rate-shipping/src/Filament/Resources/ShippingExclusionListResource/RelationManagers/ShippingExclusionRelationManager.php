<?php

namespace Lunar\Shipping\Filament\Resources\ShippingExclusionListResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Models\Product;

class ShippingExclusionRelationManager extends RelationManager
{
    protected static string $relationship = 'exclusions';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('lunarpanel.shipping::relationmanagers.exclusions.title_plural');
    }

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\MorphToSelect::make('purchasable')
                    ->types([
                        Forms\Components\MorphToSelect\Type::make(Product::class)
                            ->titleAttribute('name.en')
                            ->getSearchResultsUsing(static function (Forms\Components\Select $component, string $search): array {
                                return Product::search($search)
                                    ->get()
                                    ->mapWithKeys(fn (Product $record): array => [$record->getKey() => $record->translateAttribute('name')])
                                    ->all();
                            }),
                    ])
                    ->label(
                        __('lunarpanel.shipping::relationmanagers.exclusions.form.purchasable.label')
                    )
                    ->required()
                    ->searchable(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('purchasable.thumbnail')
                    ->collection('images')
                    ->conversion('small')
                    ->limit(1)
                    ->square()
                    ->label(''),
                \Lunar\Admin\Support\Tables\Columns\TranslatedTextColumn::make('attribute_data.name')
                    ->attributeData()
                    ->limitedTooltip()
                    ->limit(50)
                    ->label(__('lunarpanel::product.table.name.label')),
                Tables\Columns\TextColumn::make('purchasable.variants.sku')
                    ->label(__('lunarpanel::product.table.sku.label'))
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->mutateFormDataUsing(function (array $data, RelationManager $livewire) {
                    return $data;
                }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
