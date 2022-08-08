<?php

namespace GetCandy\Hub\Http\Livewire\Components\Tables;

use GetCandy\Hub\Tables\Columns\TextColumn;
use GetCandy\Hub\Tables\GetCandyTable;
use Illuminate\Contracts\Database\Query\Builder;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\EditAction;
use GetCandy\Models\Currency;
use GetCandy\Models\TaxZone;

class TaxZonesTable extends GetCandyTable
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
        return TaxZone::query();
    }

    /**
     * {@inheritDoc}
     */
    protected function getBaseTableColumns(): array
    {
        return [
            BooleanColumn::make('default'),
            TextColumn::make('name')->url(fn (TaxZone $record): string => route('hub.taxes.show', ['taxZone' => $record])),
            TextColumn::make('zone_type'),
            BooleanColumn::make('active'),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getBaseTableActions(): array
    {
        return [
            ActionGroup::make([
                EditAction::make()->url(fn (TaxZone $record): string => route('hub.taxes.show', ['taxZone' => $record])),
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
