<?php

namespace GetCandy\Hub\Http\Livewire\Components\Products\Tables;

use GetCandy\Hub\Http\Livewire\Traits\Notifies;
use GetCandy\Hub\Models\SavedSearch;
use GetCandy\Hub\Tables\Builders\ProductTypesTableBuilder;
use GetCandy\LivewireTables\Components\Columns\TextColumn;
use GetCandy\LivewireTables\Components\Columns\ImageColumn;
use GetCandy\LivewireTables\Components\Columns\BadgeColumn;
use GetCandy\LivewireTables\Components\Table;
use Illuminate\Support\Collection;

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
            )
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
