<?php

namespace Lunar\Admin\Filament\Resources\DiscountResource\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Forms\Components\Select;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Support\RelationManagers\BaseRelationManager;
use Lunar\Models\Collection;

class CollectionLimitationRelationManager extends BaseRelationManager
{
    protected static bool $isLazy = false;

    protected static string $relationship = 'collections';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('lunarpanel::collection.plural_label');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function getDefaultTable(Table $table): Table
    {

        return $table
            ->description(
                __('lunarpanel::discount.relationmanagers.collections.description')
            )
            ->paginated(false)
            ->headerActions([
                AttachAction::make()->form(fn (AttachAction $action): array => [
                    $action->getRecordSelect(),
                    Select::make('type')
                        ->options(
                            fn () => [
                                'limitation' => __('lunarpanel::discount.relationmanagers.collections.form.type.options.limitation.label'),
                                'exclusion' => __('lunarpanel::discount.relationmanagers.collections.form.type.options.exclusion.label'),
                            ]
                        )->default('limitation'),
                ])->recordTitle(function ($record) {
                    return $record->attr('name');
                })->preloadRecordSelect()
                    ->label(
                        __('lunarpanel::discount.relationmanagers.collections.actions.attach.label')
                    ),
            ])->columns([
                TextColumn::make('attribute_data.name')
                    ->label(
                        __('lunarpanel::discount.relationmanagers.collections.table.name.label')
                    )
                    ->description(fn (Collection $record): string => $record->breadcrumb->implode(' > '))
                    ->formatStateUsing(
                        fn (Model $record) => $record->attr('name')
                    ),
                TextColumn::make('pivot.type')
                    ->label(
                        __('lunarpanel::discount.relationmanagers.collections.table.type.label')
                    )->formatStateUsing(
                        fn (string $state) => __("lunarpanel::discount.relationmanagers.collections.table.type.{$state}.label")
                    ),
            ])->recordActions([
                DetachAction::make(),
            ])->toolbarActions([
                DetachBulkAction::make(),
            ]);
    }
}
