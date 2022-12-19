<?php

namespace Lunar\Hub\Http\Livewire\Components\Discounts;

use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\LivewireTables\Components\Columns\BadgeColumn;
use Lunar\LivewireTables\Components\Columns\TextColumn;
use Lunar\LivewireTables\Components\Table;
use Lunar\Models\Discount;

class DiscountsTable extends Table
{
    use Notifies;

    /**
     * {@inheritDoc}
     */
    public bool $searchable = false;

    /**
     * {@inheritDoc}
     */
    public bool $canSaveSearches = false;

    /**
     * {@inheritDoc}
     */
    public function build()
    {
        $this->tableBuilder->baseColumns([
            BadgeColumn::make('status', function ($record) {
                $active = $record->starts_at?->isPast() && ! $record->ends_at?->isPast();
                $expired = $record->ends_at?->isPast();
                $future = $record->starts_at?->isFuture();

                $status = $active ? 'active' : 'pending';

                if ($expired) {
                    $status = 'expired';
                }

                if ($future) {
                    $status = 'scheduled';
                }

                return __('adminhub::components.discounts.index.status.'.$status);
            })->states(function ($record) {
                return [
                    'info' => $record->starts_at?->isFuture(),
                    'pending' => ! $record->starts_at?->isPast(),
                    'success' => $record->starts_at?->isPast() && ! $record->ends_at?->isPast(),
                    'danger' => $record->ends_at?->isPast(),
                ];
            }),
            TextColumn::make('name')->heading(
                __('adminhub::tables.headings.name')
            )->url(function ($record) {
                return route('hub.discounts.show', $record->id);
            }),
            TextColumn::make('type', function ($record) {
                return (new $record->type)->getName();
            }),
            TextColumn::make('starts_at'),
            TextColumn::make('ends_at'),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getData()
    {
        return Discount::paginate($this->perPage);
    }
}
