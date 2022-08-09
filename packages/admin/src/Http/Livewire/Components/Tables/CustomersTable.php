<?php

namespace GetCandy\Hub\Http\Livewire\Components\Tables;

use Filament\Tables\Columns\TextColumn;
use GetCandy\Hub\Tables\GetCandyTable;
use GetCandy\Models\Customer;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class CustomersTable extends GetCandyTable
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
        return Customer::query();
    }

    /**
     * {@inheritDoc}
     */
    protected function applySearchToTableQuery(Builder $query): Builder
    {
        if (filled($searchQuery = $this->getTableSearchQuery())) {
            $query->whereIn('id', Customer::search($searchQuery)->keys());
        }

        return $query;
    }

    /**
     * {@inheritDoc}
     */
    protected function getBaseTableColumns(): array
    {
        return [
            TextColumn::make('fullName')->url(fn (Customer $record): string => route('hub.customers.show', ['customer' => $record])),
            TextColumn::make('company_name'),
            TextColumn::make('vat_no'),
            TextColumn::make('orders_count')->counts('orders'),
            TextColumn::make('users_count')->counts('users'),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getBaseTableActions(): array
    {
        return [];
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
        return [];
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
