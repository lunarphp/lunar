<?php

namespace GetCandy\Hub\Http\Livewire\Components\Tables;

use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Filters\SelectFilter;
use GetCandy\Hub\Tables\Columns\SkuColumn;
use GetCandy\Hub\Tables\Columns\TextColumn;
use GetCandy\Hub\Tables\GetCandyTable;
use GetCandy\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class ProductsTable extends GetCandyTable
{
    /**
     * {@inheritDoc}
     */
    public function isTableSearchable(): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    protected function getTableQuery(): Builder
    {
        return Product::query();
    }

    /**
     * {@inheritDoc}
     */
    protected function applySearchToTableQuery(Builder $query): Builder
    {
        if (filled($searchQuery = $this->getTableSearchQuery())) {
            $query->whereIn('id', Product::search($searchQuery)->keys());
        }

        return $query;
    }

    /**
     * {@inheritDoc}
     */
    protected function getBaseTableColumns(): array
    {
        return [
            $this->statusColumn(),
            $this->thumbnailColumn(),
            $this->attributeColumn('name')->url(fn (Product $record): string => route('hub.products.show', ['product' => $record])),
            TextColumn::make('brand'),
            TextColumn::make('productType.name'),
            SkuColumn::make('sku')->label('SKU'),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getBaseTableActions(): array
    {
        return [
            ActionGroup::make([
                RestoreAction::make(),
                EditAction::make()->url(fn (Product $record): string => route('hub.products.show', ['product' => $record])),
            ]),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getBaseTableBulkActions(): array
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    protected function getBaseTableFilters(): array
    {
        return [
            SelectFilter::make('brand')->options(
                Product::distinct()->pluck('brand')->mapWithKeys(function ($brand) {
                    return [$brand => $brand];
                }),
            ),
            $this->statusFilter(),
            $this->trashedFilter(),
        ];
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.tables.base-table')
            ->layout('adminhub::layouts.base');
    }
}
