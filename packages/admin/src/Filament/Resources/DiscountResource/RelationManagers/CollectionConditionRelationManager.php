<?php

namespace Lunar\Admin\Filament\Resources\DiscountResource\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms;
use Filament\Tables\Columns\TextColumn;
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
                AttachAction::make()->form(fn (AttachAction $action): array => [
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
                TextColumn::make('id')
                    ->label(
                        __('lunarpanel::discount.relationmanagers.collection_conditions.table.name.label')
                    )
                    ->formatStateUsing(
                        fn (Model $record) => $record->attr('name')
                    ),
            ])->actions([
                DeleteAction::make(),
            ])->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
