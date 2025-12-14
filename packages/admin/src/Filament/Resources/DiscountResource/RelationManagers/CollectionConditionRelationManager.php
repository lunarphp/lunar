<?php

namespace Lunar\Admin\Filament\Resources\DiscountResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Support\RelationManagers\BaseRelationManager;

class CollectionConditionRelationManager extends BaseRelationManager
{
    protected static bool $isLazy = false;

    protected static string $relationship = 'collections';

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
        $prefix = config('lunar.database.table_prefix');

        return $table
            ->heading(
                __('lunarpanel::discount.relationmanagers.collection_conditions.title')
            )
            ->description(
                __('lunarpanel::discount.relationmanagers.collection_conditions.description')
            )
            ->paginated(false)
            ->modifyQueryUsing(
                fn ($query) => $query->whereIn($prefix.'collection_discount.type', ['condition'])
            )
            ->headerActions([
                Tables\Actions\AttachAction::make()->form(fn (Tables\Actions\AttachAction $action): array => [
                    $action->getRecordSelect(),
                    Forms\Components\Hidden::make('type')->default('condition'),
                ])->recordTitle(function ($record) {
                    return $record->attr('name');
                })->recordSelectSearchColumns(['attribute_data->name'])
                    ->preloadRecordSelect()
                    ->label(
                        __('lunarpanel::discount.relationmanagers.collection_conditions.actions.attach.label')
                    ),
            ])->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(
                        __('lunarpanel::discount.relationmanagers.collection_conditions.table.name.label')
                    )
                    ->formatStateUsing(
                        fn (Model $record) => $record->attr('name')
                    ),
            ])->actions([
                Tables\Actions\DeleteAction::make(),
            ])->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
