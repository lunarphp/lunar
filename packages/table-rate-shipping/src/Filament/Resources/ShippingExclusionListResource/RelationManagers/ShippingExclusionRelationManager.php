<?php

namespace Lunar\Shipping\Filament\Resources\ShippingExclusionListResource\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Models\Contracts\Product as ProductContract;
use Lunar\Core\Models\Product;

class ShippingExclusionRelationManager extends RelationManager
{
    protected static string $relationship = 'exclusions';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('lunarpanel.shipping::relationmanagers.exclusions.title_plural');
    }

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\MorphToSelect::make('purchasable')
                    ->types([
                        Forms\Components\MorphToSelect\Type::make(Product::class)
                            ->titleAttribute('name')
                            ->getOptionLabelUsing(
                                fn (string $value): ?string => Product::find($value)?->translate('name')
                            )
                            ->getSearchResultsUsing(static function (Forms\Components\Select $component, string $search): array {
                                return get_search_builder(Product::class, $search)
                                    ->get()
                                    ->mapWithKeys(fn (ProductContract $record): array => [$record->getKey() => $record->translate('name')])
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
                SpatieMediaLibraryImageColumn::make('purchasable.thumbnail')
                    ->collection(config('lunar.media.collection'))
                    ->conversion('small')
                    ->limit(1)
                    ->square()
                    ->label(''),
                TextColumn::make('purchasable')
                    ->formatStateUsing(
                        fn ($state) => $state->translate('name')
                    )
                    ->limit(50)
                    ->label(__('lunarpanel::product.table.name.label')),
                TextColumn::make('purchasable.variants.sku')
                    ->label(__('lunarpanel::product.table.sku.label'))
                    ->tooltip(function (TextColumn $column, $state): ?string {

                        $skus = collect($state);

                        if ($skus->count() <= $column->getListLimit()) {
                            return null;
                        }

                        if ($skus->count() > 30) {
                            $skus = $skus->slice(0, 30);
                        }

                        return $skus->implode(', ');
                    })
                    ->listWithLineBreaks()
                    ->limitList(1)
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()->mutateFormDataUsing(function (array $data, RelationManager $livewire) {
                    return $data;
                }),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
