<?php

namespace Lunar\Admin\Filament\Resources\DiscountResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Models\Product;

class ProductRewardRelationManager extends RelationManager
{
    protected static bool $isLazy = false;

    protected static string $relationship = 'purchasables';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('lunarpanel::discount.relationmanagers.rewards.title');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {

        return $table
            ->heading(
                __('lunarpanel::discount.relationmanagers.rewards.title')
            )
            ->description(
                __('lunarpanel::discount.relationmanagers.rewards.description')
            )
            ->paginated(false)
            ->modifyQueryUsing(
                fn ($query) => $query->whereIn('type', ['reward'])
                    ->wherePurchasableType(Product::class)
                    ->whereHas('purchasable')
            )
            ->headerActions([
                Tables\Actions\CreateAction::make()->form([
                    Forms\Components\MorphToSelect::make('purchasable')
                        ->searchable(true)
                        ->types([
                            Forms\Components\MorphToSelect\Type::make(Product::class)
                                ->titleAttribute('name.en')
                                ->getSearchResultsUsing(static function (Forms\Components\Select $component, string $search): array {
                                    return get_search_builder(Product::class, $search)
                                        ->get()
                                        ->mapWithKeys(fn (Product $record): array => [$record->getKey() => $record->attr('name')])
                                        ->all();
                                }),
                        ]),
                ])->label(
                    __('lunarpanel::discount.relationmanagers.rewards.actions.attach.label')
                )->mutateFormDataUsing(function (array $data) {
                    $data['type'] = 'reward';

                    return $data;
                }),
            ])->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('purchasable.thumbnail')
                    ->collection('images')
                    ->conversion('small')
                    ->limit(1)
                    ->square()
                    ->label(''),
                Tables\Columns\TextColumn::make('purchasable.attribute_data.name')
                    ->label(
                        __('lunarpanel::discount.relationmanagers.rewards.table.name.label')
                    )
                    ->formatStateUsing(
                        fn (Model $record) => $record->purchasable->attr('name')
                    ),
            ])->actions([
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
