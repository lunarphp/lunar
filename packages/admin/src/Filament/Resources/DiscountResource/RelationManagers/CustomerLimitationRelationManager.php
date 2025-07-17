<?php

namespace Lunar\Admin\Filament\Resources\DiscountResource\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Support\RelationManagers\BaseRelationManager;

class CustomerLimitationRelationManager extends BaseRelationManager
{
    protected static bool $isLazy = false;

    protected static string $relationship = 'customers';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('lunarpanel::customer.plural_label');
    }

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
                AttachAction::make()->form(fn (AttachAction $action): array => [
                    $action->getRecordSelect(),
                ])->recordTitle(function ($record) {
                    return $record->full_name;
                })->preloadRecordSelect()
                    ->label(
                        __('lunarpanel::discount.relationmanagers.customers.actions.attach.label')
                    ),
            ])->columns([
                TextColumn::make('full_name')
                    ->label(
                        __('lunarpanel::discount.relationmanagers.customers.table.name.label')
                    ),
            ])->recordActions([
                DetachAction::make(),
            ]);
    }
}
