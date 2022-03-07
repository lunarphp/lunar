<?php

namespace GetCandy\Hub\Http\Livewire\Components\Orders;

use GetCandy\Hub\Facades\OrdersTable;
use GetCandy\Hub\Http\Livewire\Traits\Notifies;
use GetCandy\Hub\Http\Livewire\Traits\WithSavedSearches;
use GetCandy\Hub\Search\OrderSearch;
use GetCandy\Hub\Tables\Orders;
use GetCandy\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;

class OrdersIndex extends Component
{
    use WithPagination, WithSavedSearches, Notifies;

    public $perPage = 5;

    /**
     * The search term.
     *
     * @var string
     */
    public $search = '';

    /**
     * The search filters.
     *
     * @var array
     */
    public $filters = [];

    /**
     * The selected orders.
     *
     * @var array
     */
    public $selected = [];

    /**
     * Whether to select all visible orders.
     *
     * @var bool
     */
    public $selectAll = false;

    /**
     * Whether saved search should be visible.
     *
     * @var bool
     */
    public $showSaveSearch = false;

    /**
     * Whether to show the update status model.
     *
     * @var bool
     */
    public $showUpdateStatus = false;

    /**
     * The status to update orders to.
     *
     * @var string
     */
    public $status = null;

    /**
     * Define what to track in the query string.
     *
     * @var array
     */
    protected $queryString = [
        'search' => ['except' => ''],
        'page',
        'perPage',
        'filters',
    ];

    /**
     * {@inheritDoc}
     */
    public function mount()
    {
        $this->filters = array_merge([
            'status' => null,
            'to' => null,
            'from' => null,
        ], $this->filters);
    }

    /**
     * {@inheritDoc}
     */
    public function rules()
    {
        return array_merge([
            'status' => 'required',
            'filters.from' => 'nullable',
            'filters.to' => 'nullable',
            'selected' => 'nullable|array',
            'selectAll' => 'nullable',
        ], $this->withSavedSearchesValidationRules());
    }

    /**
     * Handle when search is updated.
     *
     * @return void
     */
    public function updatedSearch()
    {
        $this->setPage(1);
    }

    /**
     * Handle when selecting all orders.
     *
     * @param  bool  $val
     * @return void
     */
    public function updatedSelectAll($val)
    {
        if ($val) {
            $this->selected = $this->orders->items->pluck('id')->toArray();
        } else {
            $this->selected = [];
        }
    }

    /**
     * Return the table columns.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getColumnsProperty()
    {
        return OrdersTable::getColumns();
    }

    /**
     * Return the available filters.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAvailableFiltersProperty()
    {
        return OrdersTable::getFilters();
    }

    /**
     * Return the orders for the listing.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getOrdersProperty()
    {
        $search = new OrderSearch();

        return $search->search(
            $this->search,
            [
                'filters' => $this->filters,
            ],
            $this->perPage,
            $this->page
        );
    }

    /**
     * Export the current orders.
     *
     * @return \Symfony\Component\HttpFoundation\File\Stream
     */
    public function export()
    {
        $ids = $this->selected;

        if (! count($ids)) {
            $ids = $this->orders->items->pluck('id')->toArray();
        }

        return OrdersTable::export($ids);
    }

    /**
     * Update order status.
     *
     * @return void
     */
    public function updateStatus()
    {
        Order::whereIn('id', $this->selected)->update([
            'status' => $this->status,
        ]);

        $this->showUpdateStatus = false;
        $this->status = null;
        $this->selected = [];

        $this->notify(
            __('adminhub::notifications.orders.status_updated')
        );
    }

    /**
     * Return the configured statuses.
     *
     * @return array
     */
    public function getStatusesProperty()
    {
        return config('getcandy.orders.statuses', []);
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.orders.index')
            ->layout('adminhub::layouts.base');
    }
}
