<?php

namespace Lunar\Hub\Http\Livewire\Components\Orders;

use Illuminate\Support\Collection;
use Lunar\Facades\DB;
use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Hub\Models\SavedSearch;
use Lunar\Hub\Tables\Builders\OrdersTableBuilder;
use Lunar\LivewireTables\Components\Actions\Action;
use Lunar\LivewireTables\Components\Actions\BulkAction;
use Lunar\LivewireTables\Components\Filters\DateFilter;
use Lunar\LivewireTables\Components\Filters\SelectFilter;
use Lunar\LivewireTables\Components\Table;
use Lunar\Models\Order;
use Lunar\Models\Tag;

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
    public ?string $poll = null;

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

        $this->tableBuilder->addFilter(
            SelectFilter::make('status')->options(function () {
                $statuses = collect(
                    config('lunar.orders.statuses'),
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
            SelectFilter::make('tags')->options(function () {
                $tagTable = (new Tag)->getTable();

                $tags = DB::connection(config('lunar.database.connection'))
                ->table(config('lunar.database.table_prefix').'taggables')
                ->join($tagTable, 'tag_id', '=', "{$tagTable}.id")
                ->whereTaggableType(Order::class)
                ->distinct()
                ->pluck('value')
                ->map(function ($value) {
                    return [
                        'value' => $value,
                        'label' => $value,
                    ];
                });

                return collect([
                    null => 'None',
                ])->merge($tags);
            })->query(function ($filters, $query) {
                $value = $filters->get('tags');

                if ($value) {
                    $query->whereHas('tags', function ($query) use ($value) {
                        $query->whereValue($value);
                    });
                }
            })
        );

        $this->tableBuilder->addFilter(
            SelectFilter::make('new_returning')->options(function () {
                return collect([
                    null => 'Both',
                    'new' => 'New',
                    'returning' => 'Returning',
                ]);
            })->query(function ($filters, $query) {
                $value = $filters->get('new_returning');

                if ($value) {
                    $query->whereNewCustomer(
                        $value == 'new'
                    );
                }
            })
        );

        $this->tableBuilder->addFilter(
            DateFilter::make('placed_at')
                ->heading('Placed at')
                ->query(function ($filters, $query) {
                    $value = $filters->get('placed_at');

                    if (! $value) {
                        return $query;
                    }

                    $parts = explode(' to ', $value);

                    if (empty($parts[1])) {
                        return $query;
                    }

                    $query->whereBetween('placed_at', [
                        $parts[0],
                        $parts[1],
                    ]);
                })
        );

        $this->tableBuilder->addAction(
            Action::make('view')->label('View Order')->url(function ($record) {
                return route('hub.orders.show', $record->id);
            })
        );

        $this->tableBuilder->addBulkAction(
            BulkAction::make('update_status')
                ->label('Update Status')
                ->livewire('hub.components.tables.actions.update-status')
        );
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

        if ($this->customerId) {
            return Order::whereCustomerId($this->customerId)
                ->paginate($this->perPage);
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
