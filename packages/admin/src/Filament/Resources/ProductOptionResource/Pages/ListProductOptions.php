<?php

namespace Lunar\Admin\Filament\Resources\ProductOptionResource\Pages;

use Filament\Actions;
use Illuminate\Database\Eloquent\Builder;
use Lunar\Admin\Filament\Resources\ProductOptionResource;
use Lunar\Admin\Support\Pages\BaseListRecords;
use Lunar\Models\ProductOption;

class ListProductOptions extends BaseListRecords
{
    protected static string $resource = ProductOptionResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function applySearchToTableQuery(Builder $query): Builder
    {
        $this->applyColumnSearchesToTableQuery($query);

        if (filled($search = $this->getTableSearch())) {
            $query->whereIn(
                'id',
                collect(ProductOption::search($search)->keys())->map(
                    fn ($result) => str_replace(ProductOption::class.'::', '', $result)
                )
            );
        }

        return $query;
    }
}
