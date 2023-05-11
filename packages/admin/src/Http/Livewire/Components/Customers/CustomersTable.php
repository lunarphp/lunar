<?php

namespace Lunar\Hub\Http\Livewire\Components\Customers;

use Illuminate\Support\Collection;
use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Hub\Models\SavedSearch;
use Lunar\Hub\Tables\Builders\CustomersTableBuilder;
use Lunar\LivewireTables\Components\Columns\TextColumn;
use Lunar\LivewireTables\Components\Table;

class CustomersTable extends Table
{
    use Notifies;

    /**
     * {@inheritDoc}
     */
    protected $tableBuilderBinding = CustomersTableBuilder::class;

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
            TextColumn::make('name', function ($record) {
                return $record->fullName;
            })->url(function ($record) {
                return route('hub.customers.show', $record->id);
            })->heading(
                __('adminhub::tables.headings.name')
            ),
            TextColumn::make('company_name')->heading(
                __('adminhub::tables.headings.company_name')
            ),
            TextColumn::make('vat_no')->heading(
                __('adminhub::tables.headings.vat_no')
            ),
            TextColumn::make('account_ref')->heading(
                __('adminhub::tables.headings.account_ref')
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
