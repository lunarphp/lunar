<?php

namespace Lunar\Filament\RelationManagers\Discount;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Models\Collection;
use Lunar\Filament\Forms\Components\CollectionSelect;
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
                    ->with(['discountable' => fn ($morphTo) => $morphTo->morphWith([
                        Collection::class => ['group', 'ancestors'],
                    ])])
            )
            ->headerActions([
                CreateAction::make()->schema([
                    CollectionSelect::make('discountable_id')
                        ->label(__('lunar-filament::collection.label'))
                        ->required(),
                    Hidden::make('discountable_type')
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
                    ->description(
                        fn (Model $record): string => collect([$record->discountable?->group?->name])
                            ->concat($record->discountable?->breadcrumb ?? [])
                            ->filter()
                            ->implode(' > ')
                    )
                    ->formatStateUsing(
                        fn (Model $record) => $record->discountable?->translate('name')
                    ),
            ])->recordActions([
                DeleteAction::make(),
            ])->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
