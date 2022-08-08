<?php

namespace GetCandy\Hub\Http\Livewire\Components\Tables;

use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\TextColumn;
use GetCandy\Hub\Models\Staff;
use GetCandy\Hub\Tables\Columns\GravatarColumn;
use GetCandy\Hub\Tables\GetCandyTable;
use Illuminate\Contracts\Database\Query\Builder;

class StaffTable extends GetCandyTable
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
        return Staff::query();
    }

    /**
     * {@inheritDoc}
     */
    protected function applySearchToTableQuery(Builder $query): Builder
    {
        if (filled($searchQuery = $this->getTableSearchQuery())) {
            $query->search($searchQuery, true);
        }

        return $query;
    }

    /**
     * {@inheritDoc}
     */
    protected function getBaseTableColumns(): array
    {
        return [
            GravatarColumn::make('email as em')->label(''),
            BooleanColumn::make('admin'),
            TextColumn::make('firstname'),
            TextColumn::make('lastname'),
            TextColumn::make('email'),
            // OrderStatusColumn::make('status'),
            // TextColumn::make('reference')->url(fn (Order $record): string => route('hub.orders.show', ['order' => $record])),
            // TextColumn::make('billingAddress.fullName')->label('Customer'),
            // TextColumn::make('billingAddress.company_name')->label('Company Name'),
            // TextColumn::make('billingAddress.contact_email')->label('Billing Email'),
            // PriceColumn::make('total')->label('Total'),
            // TextColumn::make('placed_at')->dateTime(),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getBaseTableActions(): array
    {
        return [
            EditAction::make()->url(fn (Staff $record): string => route('hub.staff.show', ['staff' => $record])),
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
