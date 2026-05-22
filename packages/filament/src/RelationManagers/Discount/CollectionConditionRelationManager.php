<?php

namespace Lunar\Filament\RelationManagers\Discount;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\Contracts\Collection as CollectionContract;
use Lunar\Filament\RelationManagers\BaseRelationManager;

class CollectionConditionRelationManager extends BaseRelationManager
{
    protected static bool $isLazy = false;

    protected static string $relationship = 'discountables';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('lunar-filament::discount.relationmanagers.collection_conditions.title');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function getDefaultTable(Table $table): Table
    {
        return $table
            ->heading(
                __('lunar-filament::discount.relationmanagers.collection_conditions.title')
            )
            ->description(
                __('lunar-filament::discount.relationmanagers.collection_conditions.description')
            )
            ->paginated(false)
            ->modifyQueryUsing(
                fn ($query) => $query->where('type', 'condition')
                    ->where('discountable_type', Collection::morphName())
                    ->whereHas('discountable')
            )
            ->headerActions([
                CreateAction::make()->schema([
                    Select::make('discountable_id')
                        ->label(__('lunar-filament::collection.singular_label'))
                        ->required()
                        ->searchable()
                        ->getSearchResultsUsing(static function (string $search): array {
                            return get_search_builder(Collection::modelClass(), $search)
                                ->get()
                                ->mapWithKeys(fn (CollectionContract $record): array => [$record->getKey() => $record->attr('name')])
                                ->all();
                        })
                        ->getOptionLabelUsing(function ($value): string {
                            return Collection::modelClass()::find($value)?->attr('name') ?? $value;
                        }),
                    Forms\Components\Hidden::make('discountable_type')
                        ->default(Collection::morphName()),
                ])->label(
                    __('lunar-filament::discount.relationmanagers.collection_conditions.actions.attach.label')
                )->mutateDataUsing(function (array $data) {
                    $data['type'] = 'condition';

                    return $data;
                }),
            ])->columns([
                TextColumn::make('discountable.id')
                    ->label(
                        __('lunar-filament::discount.relationmanagers.collection_conditions.table.name.label')
                    )
                    ->formatStateUsing(
                        fn (Model $record) => $record->discountable?->attr('name')
                    ),
            ])->recordActions([
                DeleteAction::make(),
            ])->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
