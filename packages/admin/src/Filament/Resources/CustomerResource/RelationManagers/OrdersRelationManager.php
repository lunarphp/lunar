<?php

namespace Lunar\Admin\Filament\Resources\CustomerResource\RelationManagers;

use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Resources\OrderResource;
use Lunar\Admin\Filament\Resources\OrderResource\Pages\ManageOrder;
use Lunar\Admin\Support\RelationManagers\BaseRelationManager;
use Lunar\Models\Order;

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
            OrderResource::getTableColumns()
        )->actions([
            Tables\Actions\Action::make('viewOrder')
                ->url(fn (Order $record): string => ManageOrder::getUrl(['record' => $record])),
        ]);
    }
}
