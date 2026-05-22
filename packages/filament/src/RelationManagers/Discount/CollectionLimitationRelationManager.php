<?php

namespace Lunar\Filament\RelationManagers\Discount;

use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Models\Collection;
use Lunar\Filament\RelationManagers\BaseRelationManager;

class CollectionLimitationRelationManager extends BaseRelationManager
{
    protected static bool $isLazy = false;

    protected static string $relationship = 'collections';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('lunar-filament::collection.plural_label');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function getDefaultTable(Table $table): Table
    {
        $prefix = config('lunar.database.table_prefix');

        return $table
            ->description(
                __('lunar-filament::discount.relationmanagers.collections.description')
            )
            ->modifyQueryUsing(
                fn ($query) => $query->whereIn($prefix.'collection_discount.type', ['limitation', 'exclusion'])
            )
            ->paginated(false)
            ->headerActions([
                AttachAction::make()->form(fn (AttachAction $action): array => [
                    $action->getRecordSelect(),
                    Select::make('type')
                        ->options(
                            fn () => [
                                'limitation' => __('lunar-filament::discount.relationmanagers.collections.form.type.options.limitation.label'),
                                'exclusion' => __('lunar-filament::discount.relationmanagers.collections.form.type.options.exclusion.label'),
                            ]
                        )->default('limitation'),
                ])->recordTitle(function ($record) {
                    return $record->attr('name');
                })->recordSelectSearchColumns(['attribute_data->name'])
                    ->preloadRecordSelect()
                    ->label(
                        __('lunar-filament::discount.relationmanagers.collections.actions.attach.label')
                    ),
            ])->columns([
                TextColumn::make('attribute_data.name')
                    ->label(
                        __('lunar-filament::discount.relationmanagers.collections.table.name.label')
                    )
                    ->description(fn (Collection $record): string => $record->breadcrumb->implode(' > '))
                    ->formatStateUsing(
                        fn (Model $record) => $record->attr('name')
                    ),
                TextColumn::make('pivot.type')
                    ->label(
                        __('lunar-filament::discount.relationmanagers.collections.table.type.label')
                    )->formatStateUsing(
                        fn (string $state) => __("lunar-filament::discount.relationmanagers.collections.table.type.{$state}.label")
                    ),
            ])->recordActions([
                DetachAction::make(),
            ])->toolbarActions([
                DetachBulkAction::make(),
            ]);
    }
}
