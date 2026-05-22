<?php

namespace Lunar\Filament\RelationManagers\Customer;

use Filament\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Resources\OrderResource\Pages\ManageOrder;
use Lunar\Filament\Tables\Order\OrderTable;
use Lunar\Admin\Support\RelationManagers\BaseRelationManager;
use Lunar\Core\Models\Contracts\Order as OrderContract;

class OrdersRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'orders';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('lunarpanel::order.plural_label');
    }

    public function getDefaultTable(Table $table): Table
    {
        return $table->columns(
            OrderTable::getColumns()
        )->modifyQueryUsing(
            fn (Builder $query): Builder => $query->with(['currency'])
        )->recordActions([
            Action::make('viewOrder')
                ->url(fn (OrderContract $record): string => ManageOrder::getUrl(['record' => $record])),
        ]);
    }
}
