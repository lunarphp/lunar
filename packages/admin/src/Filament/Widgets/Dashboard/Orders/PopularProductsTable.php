<?php

namespace Lunar\Admin\Filament\Widgets\Dashboard\Orders;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\OrderLine;

class PopularProductsTable extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading(
                fn () => __('lunarpanel::widgets.dashboard.orders.popular_products.heading')
            )
            ->description(
                fn () => __('lunarpanel::widgets.dashboard.orders.popular_products.description')
            )
            ->poll('60s')
            ->query(function () {
                return OrderLine::query()->with(['currency'])->whereHas('order', function ($relation) {
                    $relation->whereBetween('placed_at', [
                        now()->subYear()->startOfDay(),
                        now()->endOfDay(),
                    ]);
                })->select(
                    DB::RAW('MAX(id) as id'),
                    DB::RAW('MAX(order_id) as order_id'),
                    DB::RAW('COUNT(id) as quantity'),
                    DB::RAW('SUM(sub_total) as sub_total'),
                    DB::RAW('MAX(description) as description'),
                    'identifier',
                )->groupBy('identifier', 'purchasable_id')
                    ->whereType('physical');
            })->defaultSort('quantity', 'desc')
            ->defaultKeySort(false)
            ->columns([
                TextColumn::make('description'),
                TextColumn::make('identifier'),
                TextColumn::make('quantity'),
                TextColumn::make('sub_total')->formatStateUsing(fn ($state): string => $state->formatted),
            ]);
    }
}
