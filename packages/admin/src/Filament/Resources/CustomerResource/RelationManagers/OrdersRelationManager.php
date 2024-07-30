<?php

namespace Lunar\Admin\Filament\Resources\CustomerResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Lunar\Admin\Filament\Resources\OrderResource;
use Lunar\Models\Order;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    public function table(Table $table): Table
    {
        return $table->columns(
            OrderResource::getTableColumns()
        )->actions([
            Tables\Actions\Action::make('viewOrder')
                ->url(fn (Order $record): string => route('filament.lunar.resources.orders.order', $record)),
        ]);
    }
}
