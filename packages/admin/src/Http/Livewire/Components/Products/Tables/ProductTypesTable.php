<?php

namespace Lunar\Hub\Http\Livewire\Components\Products\Tables;

use Illuminate\Support\Collection;
use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Hub\Models\SavedSearch;
use Lunar\Hub\Tables\Builders\ProductTypesTableBuilder;
use Lunar\LivewireTables\Components\Columns\BadgeColumn;
use Lunar\LivewireTables\Components\Columns\ImageColumn;
use Lunar\LivewireTables\Components\Columns\TextColumn;
use Lunar\LivewireTables\Components\Table;

class ProductTypesTable extends Table
{
    use Notifies;

    /**
     * {@inheritDoc}
     */
    protected $tableBuilderBinding = ProductTypesTableBuilder::class;

    /**
     * {@inheritDoc}
     */
    public bool $searchable = true;

    /**
     * {@inheritDoc}
     */
    public function build()
    {
        $this->tableBuilder->baseColumns([
            TextColumn::make('name')->heading(
                __('adminhub::tables.headings.name')
            )->url(function ($record) {
                return route('hub.product-type.show', $record->id);
            }),
            TextColumn::make('product_count', function ($record) {
                return $record->products_count;
            })->heading(
                __('adminhub::tables.headings.product_count')
            ),
            TextColumn::make('mapped_attributes_count', function ($record) {
                return $record->mapped_attributes_count;
            })->heading(
                __('adminhub::tables.headings.mapped_attributes_count')
            ),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getData()
    {
        $filters = $this->filters;
        $query = $this->query;

        return $this->tableBuilder
        ->searchTerm($query)
        ->queryStringFilters($filters)
        ->perPage($this->perPage)
        ->getData();
    }
}
