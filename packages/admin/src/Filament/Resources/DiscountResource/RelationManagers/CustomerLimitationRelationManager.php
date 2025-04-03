<?php

namespace Lunar\Admin\Filament\Resources\DiscountResource\RelationManagers;

use Filament\Tables;
use Filament\Tables\Table;
use Lunar\Admin\Support\RelationManagers\BaseRelationManager;

class CustomerLimitationRelationManager extends BaseRelationManager
{
    protected static bool $isLazy = false;

    protected static string $relationship = 'customers';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function getDefaultTable(Table $table): Table
    {

        return $table
            ->description(
                __('lunarpanel::discount.relationmanagers.customers.description')
            )
            ->paginated(false)
            ->headerActions([
                Tables\Actions\AttachAction::make()->form(fn (Tables\Actions\AttachAction $action): array => [
                    $action->getRecordSelect(),
                ])->recordTitle(function ($record) {
                    return $record->full_name;
                })->preloadRecordSelect()
                    ->label(
                        __('lunarpanel::discount.relationmanagers.customers.actions.attach.label')
                    ),
            ])->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label(
                        __('lunarpanel::discount.relationmanagers.customers.table.name.label')
                    ),
            ])->actions([
                Tables\Actions\DetachAction::make(),
            ]);
    }
}
