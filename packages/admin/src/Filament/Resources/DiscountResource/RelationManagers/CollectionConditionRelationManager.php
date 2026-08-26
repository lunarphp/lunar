<?php

namespace Lunar\Admin\Filament\Resources\DiscountResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Lunar\Admin\Support\Concerns\RelationManagers\SearchesCollections;
use Lunar\Admin\Support\RelationManagers\BaseRelationManager;
use Lunar\Models\Collection;

class CollectionConditionRelationManager extends BaseRelationManager
{
    use SearchesCollections;

    protected static bool $isLazy = false;

    protected static string $relationship = 'discountables';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('lunarpanel::discount.relationmanagers.collection_conditions.title');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function getDefaultTable(Table $table): Table
    {
        return $table
            ->heading(
                __('lunarpanel::discount.relationmanagers.collection_conditions.title')
            )
            ->description(
                __('lunarpanel::discount.relationmanagers.collection_conditions.description')
            )
            ->paginated(false)
            ->modifyQueryUsing(
                fn ($query) => $query->where('type', 'condition')
                    ->where('discountable_type', Collection::morphName())
                    ->whereHas('discountable')
                    ->with(['discountable' => fn (MorphTo $morphTo) => $morphTo->morphWith([
                        Collection::modelClass() => ['group', 'ancestors'],
                    ])])
            )
            ->headerActions([
                CreateAction::make()->schema([
                    Select::make('discountable_id')
                        ->label(__('lunarpanel::collection.singular_label'))
                        ->required()
                        ->searchable()
                        ->getSearchResultsUsing(
                            static fn (Select $component, string $search): array => static::getCollectionSearchResults($search, $component->getOptionsLimit())
                        )
                        ->getOptionLabelUsing(function ($value): string {
                            $record = static::withCollectionPathRelations(Collection::modelClass()::query())->find($value);

                            return $record ? static::getCollectionOptionLabel($record) : $value;
                        }),
                    Forms\Components\Hidden::make('discountable_type')
                        ->default(Collection::morphName()),
                ])->label(
                    __('lunarpanel::discount.relationmanagers.collection_conditions.actions.attach.label')
                )->mutateDataUsing(function (array $data) {
                    $data['type'] = 'condition';

                    return $data;
                }),
            ])->columns([
                TextColumn::make('discountable.id')
                    ->label(
                        __('lunarpanel::discount.relationmanagers.collection_conditions.table.name.label')
                    )
                    ->formatStateUsing(
                        fn (Model $record) => $record->discountable?->attr('name')
                    )
                    ->description(
                        fn (Model $record) => $record->discountable ? static::getCollectionPath($record->discountable) : null
                    ),
            ])->recordActions([
                DeleteAction::make(),
            ])->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
