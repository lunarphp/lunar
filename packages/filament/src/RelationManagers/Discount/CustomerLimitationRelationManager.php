<?php

namespace Lunar\Filament\RelationManagers\Discount;

use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Filament\Forms\Components\CustomerSelect;
use Lunar\Filament\RelationManagers\BaseRelationManager;

class CustomerLimitationRelationManager extends BaseRelationManager
{
    protected static bool $isLazy = false;

    protected static string $relationship = 'customers';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('lunar-filament::customer.plural_label');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function getDefaultTable(Table $table): Table
    {

        return $table
            ->description(
                __('lunar-filament::discount.relationmanagers.customers.description')
            )
            ->paginated(false)
            ->headerActions([
                AttachAction::make()
                    ->recordSelect(fn (Select $select) => CustomerSelect::applyTo($select))
                    ->recordTitle(fn ($record) => $record->full_name)
                    ->preloadRecordSelect()
                    ->label(
                        __('lunar-filament::discount.relationmanagers.customers.actions.attach.label')
                    ),
            ])->columns([
                TextColumn::make('full_name')
                    ->label(
                        __('lunar-filament::discount.relationmanagers.customers.table.name.label')
                    ),
            ])->recordActions([
                DetachAction::make(),
            ]);
    }
}
