<?php

namespace GetCandy\Hub\Http\Livewire\Components\Orders;

use GetCandy\Hub\Http\Livewire\Traits\Notifies;
use GetCandy\Hub\Models\SavedSearch;
use GetCandy\Hub\Tables\Builders\OrdersTableBuilder;
use GetCandy\LivewireTables\Components\Actions\Action;
use GetCandy\LivewireTables\Components\Columns\TextColumn;
use GetCandy\LivewireTables\Components\Filters\SelectFilter;
use GetCandy\LivewireTables\Components\Filters\DateFilter;
use GetCandy\LivewireTables\Components\Table;
use GetCandy\Models\Order;
use Illuminate\Support\Collection;

class OrdersTable extends Table
{
    use Notifies;

    /**
     * {@inheritDoc}
     */
    protected $tableBuilderBinding = OrdersTableBuilder::class;

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
    public ?string $poll = '2s';

    /**
     * The customer ID to hard filter results by.
     *
     * @var string|int
     */
    public $customerId = null;

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
        $this->filters['placed_at'] = $this->filters['placed_at'] ?? null;

        $this->tableBuilder->baseColumns([
            TextColumn::make('status')->sortable(true)->viewComponent('hub::orders.status'),
            TextColumn::make('reference')->value(function ($record) {
                return $record->reference;
            })->url(function ($record) {
                return route('hub.orders.show', $record->id);
            }),
            TextColumn::make('customer_reference')->heading('Customer Reference')->value(function ($record) {
                return $record->customer_reference;
            }),
            TextColumn::make('customer')->value(function ($record) {
                return $record->billingAddress?->fullName;
            }),
            TextColumn::make('postcode')->value(function ($record) {
                return $record->billingAddress?->postcode;
            }),
            TextColumn::make('email')->value(function ($record) {
                return $record->billingAddress?->contact_email;
            }),
            TextColumn::make('phone')->value(function ($record) {
                return $record->billingAddress?->contact_phone;
            }),
            TextColumn::make('total')->value(function ($record) {
                return $record->total->formatted;
            }),
            TextColumn::make('date')->value(function ($record) {
                return $record->placed_at?->format('Y/m/d @ H:ma');
            }),
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

        $this->tableBuilder->addFilter(
            DateFilter::make('placed_at')
                ->heading('Placed at')
                ->query(function ($filters, $query) {
                    $value = $filters->get('placed_at');

                    if (!$value) {
                        return $query;
                    }

                    $parts = explode(' to ', $value);

                    if (empty($parts[1])) {
                        return $query;
                    }

                    $query->whereBetween('placed_at', [
                        $parts[0],
                        $parts[1]
                    ]);

                    // [$from, $to] = explode(' to ', $value);
                    // dd($from, $to);
                })
        );

        $this->tableBuilder->addAction(
            Action::make('view')->label('View Order')->url(function ($record) {
                return route('hub.orders.show', $record->id);
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

        if ($this->customerId) {
            return Order::whereCustomerId($this->customerId)
                ->paginate($this->perPage);
        }

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
            ->sort(
                $this->sortField ?: 'placed_at',
                $this->sortDir ?: 'desc',
            )->getData();
    }
}
