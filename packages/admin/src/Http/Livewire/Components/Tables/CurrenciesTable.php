<?php

namespace GetCandy\Hub\Http\Livewire\Components\Tables;

use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\BooleanColumn;
use GetCandy\Hub\Tables\Columns\TextColumn;
use GetCandy\Hub\Tables\GetCandyTable;
use GetCandy\Models\Currency;
use Illuminate\Contracts\Database\Query\Builder;

class CurrenciesTable extends GetCandyTable
{
    /**
     * {@inheritDoc}
     */
    public function isTableSearchable(): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    protected function getTableQuery(): Builder
    {
        return Currency::query();
    }

    /**
     * {@inheritDoc}
     */
    protected function getBaseTableColumns(): array
    {
        return [
            BooleanColumn::make('default'),
            TextColumn::make('name')->url(fn (Currency $record): string => route('hub.currencies.show', ['currency' => $record])),
            TextColumn::make('code'),
            TextColumn::make('exchange_rate'),
            BooleanColumn::make('enabled'),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getBaseTableActions(): array
    {
        return [
            ActionGroup::make([
                EditAction::make()->url(fn (Currency $record): string => route('hub.currencies.show', ['currency' => $record])),
            ]),
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
     * {@inheritDoc}
     */
    protected function getBaseTableBulkActions(): array
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
