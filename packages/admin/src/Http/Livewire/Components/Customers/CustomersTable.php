<?php

namespace GetCandy\Hub\Http\Livewire\Components\Customers;

use GetCandy\Hub\Http\Livewire\Traits\Notifies;
use GetCandy\Hub\Models\SavedSearch;
use GetCandy\Hub\Tables\Builders\CustomersTableBuilder;
use GetCandy\Hub\Tables\Builders\OrdersTableBuilder;
use GetCandy\LivewireTables\Components\Actions\Action;
use GetCandy\LivewireTables\Components\Actions\BulkAction;
use GetCandy\LivewireTables\Components\Columns\TextColumn;
use GetCandy\LivewireTables\Components\Filters\SelectFilter;
use GetCandy\LivewireTables\Components\Table;
use Illuminate\Support\Collection;

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
    public $searchable = true;

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
            }),
            TextColumn::make('company_name'),
            // TextColumn::make('reference')->value(function ($record) {
            //     return $record->reference;
            // })->url(function ($record) {
            //     return route('hub.orders.show', $record->id);
            // }),
            // TextColumn::make('customer_reference')->heading('Customer Reference')->value(function ($record) {
            //     return $record->customer_reference;
            // }),
            // TextColumn::make('customer')->value(function ($record) {
            //     return $record->billingAddress?->fullName;
            // }),
            // TextColumn::make('postcode')->value(function ($record) {
            //     return $record->billingAddress?->postcode;
            // }),
            // TextColumn::make('email')->value(function ($record) {
            //     return $record->billingAddress?->contact_email;
            // }),
            // TextColumn::make('phone')->value(function ($record) {
            //     return $record->billingAddress?->contact_phone;
            // }),
            // TextColumn::make('total')->value(function ($record) {
            //     return $record->total->formatted;
            // }),
            // TextColumn::make('date')->value(function ($record) {
            //     return $record->placed_at?->format('Y/m/d @ H:ma');
            // }),
        ]);

        $this->tableBuilder->addFilter(
            SelectFilter::make('status')->options(function () {
                $statuses = collect(
                    config('getcandy.orders.statuses'),
                    []
                )->mapWithKeys(fn ($status, $key) => [$key => $status['label']]);

                return collect([
                    null => 'All Statuses',
                ])->merge($statuses);
            })->query(function ($filters, $query) {
                $value = $filters->get('status');

                if ($value) {
                    $query->whereStatus($value);
                }
            })
        );

        $this->tableBuilder->addAction(
            Action::make('view')->label('View Order')->url(function ($record) {
                return route('hub.products.show', $record->id);
            })
        );
    }

    /**
     * Remove a saved search record.
     *
     * @param int $id
     *
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

        return $this->tableBuilder->getData(
            $query,
            $filters,
            $this->sortField ?: 'placed_at',
            $this->sortDir ?: 'desc',
        );
    }
}
