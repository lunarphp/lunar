<?php

namespace GetCandy\Hub\Http\Livewire\Components\Tables;

use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\EditAction;
use GetCandy\Hub\Tables\Columns\TextColumn;
use GetCandy\Hub\Tables\GetCandyTable;
use GetCandy\Models\ProductType;
use Illuminate\Database\Eloquent\Builder;

class ProductTypesTable extends GetCandyTable
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
        return ProductType::query();
    }

    /**
     * {@inheritDoc}
     */
    protected function applySearchToTableQuery(Builder $query): Builder
    {
        if (filled($searchQuery = $this->getTableSearchQuery())) {
            $query->where('name', 'LIKE', '%'.$searchQuery.'%');
        }

        return $query;
    }

    /**
     * {@inheritDoc}
     */
    protected function getBaseTableColumns(): array
    {
        return [
            TextColumn::make('name')->url(fn (ProductType $record): string => route('hub.product-type.show', ['productType' => $record])),
            TextColumn::make('mapped_attributes_count')->counts('mappedAttributes'),
            TextColumn::make('products_count')->counts('products'),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getBaseTableActions(): array
    {
        return [
            ActionGroup::make([
                EditAction::make()->url(fn (ProductType $record): string => route('hub.product-type.show', ['productType' => $record])),
            ]),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getBaseTableBulkActions(): array
    {
        return [
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getBaseTableFilters(): array
    {
        return [
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
