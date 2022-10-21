<?php

namespace Lunar\Hub\Http\Livewire\Components\Products\Tables;

use Illuminate\Support\Collection;
use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Hub\Models\SavedSearch;
use Lunar\Hub\Tables\Builders\ProductsTableBuilder;
use Lunar\LivewireTables\Components\Columns\BadgeColumn;
use Lunar\LivewireTables\Components\Columns\ImageColumn;
use Lunar\LivewireTables\Components\Columns\TextColumn;
use Lunar\LivewireTables\Components\Table;

class ProductsTable extends Table
{
    use Notifies;

    /**
     * {@inheritDoc}
     */
    protected $tableBuilderBinding = ProductsTableBuilder::class;

    /**
     * {@inheritDoc}
     */
    public bool $searchable = true;

    /**
     * {@inheritDoc}
     */
    public bool $canSaveSearches = true;

    /**
     * {@inheritDoc}
     */
    protected $listeners = [
        'saveSearch' => 'handleSaveSearch',
    ];

    /**
     * {@inheritDoc}
     */
    public function build()
    {
        $this->tableBuilder->baseColumns([
            BadgeColumn::make('status', function ($record) {
                return __(
                    'adminhub::components.products.index.'.($record->deleted_at ? 'deleted' : $record->status)
                );
            })->states(function ($record) {
                return [
                    'success' => $record->status == 'published' && ! $record->deleted_at,
                    'warning' => $record->status == 'draft' && ! $record->deleted_at,
                    'danger' => (bool) $record->deleted_at,
                ];
            }),
            ImageColumn::make('thumbnail', function ($record) {
                if ($record->thumbnail) {
                    return $record->thumbnail->getUrl('small');
                }

                $variant = $record->variants->first(function ($variant) {
                    return $variant->thumbnail;
                });

                return $variant?->thumbnail?->getUrl('small');
            })->heading(false),
            TextColumn::make('name', function ($record) {
                return $record->translateAttribute('name');
            })->url(function ($record) {
                return route('hub.products.show', $record->id);
            })->heading(
                __('adminhub::tables.headings.name')
            ),
            TextColumn::make('brand.name', function ($record) {
                return $record->brand?->name;
            })->heading(
                __('adminhub::tables.headings.brand')
            ),
            TextColumn::make('sku', function ($record) {
                $skus = $record->variants->pluck('sku');

                if ($skus->count() > 1) {
                    return 'Multiple';
                }

                return $skus->first();
            })->heading(
                __('adminhub::tables.headings.sku')
            ),
            TextColumn::make('stock', function ($record) {
                return $record->variants->sum('stock');
            })->heading(
                __('adminhub::tables.headings.stock')
            ),
            TextColumn::make('productType.name', function ($record) {
                return $record->productType->name;
            })->heading(
                __('adminhub::tables.headings.product_type')
            ),
        ]);
    }

    /**
     * Remove a saved search record.
     *
     * @param  int  $id
     * @return void
     */
    public function deleteSavedSearch($id)
    {
        SavedSearch::destroy($id);

        $this->resetSavedSearch();

        $this->notify(
            __('adminhub::notifications.saved_searches.deleted')
        );
    }

    /**
     * Save a search.
     *
     * @return void
     */
    public function saveSearch()
    {
        $this->validateOnly('savedSearchName', [
            'savedSearchName' => 'required',
        ]);

        auth()->getUser()->savedSearches()->create([
            'name' => $this->savedSearchName,
            'term' => $this->query,
            'component' => $this->getName(),
            'filters' => $this->filters,
        ]);

        $this->notify('Search saved');

        $this->savedSearchName = null;

        $this->emit('savedSearch');
    }

    /**
     * Return the saved searches available to the table.
     *
     * @return Collection
     */
    public function getSavedSearchesProperty(): Collection
    {
        return auth()->getUser()->savedSearches()->whereComponent(
            $this->getName()
        )->get()->map(function ($savedSearch) {
            return [
                'key' => $savedSearch->id,
                'label' => $savedSearch->name,
                'filters' => $savedSearch->filters,
                'query' => $savedSearch->term,
            ];
        });
    }

    /**
     * {@inheritDoc}
     */
    public function getData()
    {
        $filters = $this->filters;
        $query = $this->query;

        if ($this->savedSearch) {
            $search = $this->savedSearches->first(function ($search) {
                return $search['key'] == $this->savedSearch;
            });

            if ($search) {
                $filters = $search['filters'];
                $query = $search['query'];
            }
        }

        return $this->tableBuilder
            ->searchTerm($query)
            ->queryStringFilters($filters)
            ->perPage($this->perPage)
            ->getData();
    }
}
