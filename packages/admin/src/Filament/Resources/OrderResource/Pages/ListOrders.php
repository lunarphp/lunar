<?php

namespace Lunar\Admin\Filament\Resources\OrderResource\Pages;

use Filament\Resources\Components\Tab;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Lunar\Admin\Filament\Resources\OrderResource;
use Lunar\Admin\Support\Pages\BaseListRecords;

class ListOrders extends BaseListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $statuses = collect(
            config('lunar.orders.statuses', [])
        )->filter(
            fn ($config) => $config['favourite'] ?? false
        );

        return [
            'all' => Tab::make('All'),
            ...collect($statuses)->mapWithKeys(
                fn ($config, $status) => [
                    $status => Tab::make($config['label'])
                        ->modifyQueryUsing(fn (Builder $query) => $query->where('status', $status)),
                ]
            ),
        ];
    }

    protected function paginateTableQuery(Builder $query): Paginator
    {
        return $query->simplePaginate($this->getTableRecordsPerPage());
    }

    public function getMaxContentWidth(): ?string
    {
        return 'full';
    }
}
