<?php

namespace Lunar\Filament\RelationManagers\Customer;

use Filament\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Models\Order;
use Lunar\Filament\RelationManagers\BaseRelationManager;
use Lunar\Filament\Support\RecordUrls;
use Lunar\Filament\Tables\Order\OrderTable;

class OrdersRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'orders';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('lunar-filament::order.plural_label');
    }

    public function getDefaultTable(Table $table): Table
    {
        return $table->columns(
            OrderTable::getColumns()
        )->modifyQueryUsing(
            fn (Builder $query): Builder => $query->with(['currency'])
        )->recordActions([
            Action::make('viewOrder')
                ->url(fn (Order $record): ?string => RecordUrls::for('order', $record)),
        ]);
    }
}
