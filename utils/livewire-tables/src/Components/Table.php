<?php

namespace Lunar\LivewireTables\Components;

use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;
use Lunar\LivewireTables\Components\Concerns\HasSavedSearches;
use Lunar\LivewireTables\Components\Concerns\HasSortableColumns;
use Lunar\LivewireTables\Support\TableBuilderInterface;

class Table extends Component
{
    use WithPagination,
        HasSavedSearches,
        HasSortableColumns;

    /**
     * The binding to use when building out the table.
     *
     * @var string
     */
    protected $tableBuilderBinding = TableBuilderInterface::class;

    public ?string $poll = null;

    /**
     * Whether this table should use pagination.
     *
     * @var bool
     */
    public $hasPagination = true;

    /**
     * Whether this table is searchable.
     */
    public bool $searchable = false;

    /**
     * If the table should show filters
     */
    public bool $filterable = true;

    /**
     * The search query
     *
     * @var string|null
     */
    public $query = null;

    /**
     * The applied filters.
     */
    public array $filters = [];

    /**
     * The number of records per page.
     */
    public int $perPage = 50;

    /**
     * {@inheritDoc}
     */
    protected $queryString = [
        'perPage',
        'sortField',
        'sortDir',
        'query',
        'filters',
        'savedSearch',
    ];

    /**
     * {@inheritDoc}
     */
    public function getListeners()
    {
        return [
            'sort',
            'bulkAction.reset' => '$refresh',
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function mount()
    {
        $this->build();
    }

    /**
     * {@inheritDoc}
     */
    public function hydrate()
    {
        $this->build();
    }

    /**
     * Build the table.
     *
     * @return void
     */
    public function build()
    {
        //
    }

    public function updatedQuery()
    {
        $this->resetPage();
        $this->resetSavedSearch();
    }

    public function updatedFilters()
    {
        $this->resetPage();
        $this->resetSavedSearch();
    }

    public function updatedPage($page)
    {
        $this->emit('updatedPage', $page);
    }

    public function updatedSelected($value)
    {
        $this->emit('table.selectedRows', $value);
    }

    public function resetBulkActions()
    {
        $this->selected = [];
    }

    /**
     * Return the rows available to the table.
     *
     * @return Collection
     */
    public function getRowsProperty()
    {
        return $this->getData();
    }

    /**
     * Return the table data.
     *
     * @return mixed
     */
    public function getData()
    {
        return collect();
    }

    /**
     * Return the table manifest.
     *
     * @return TableManifest
     */
    public function getTableBuilderProperty()
    {
        return app($this->tableBuilderBinding);
    }

    /**
     * Return the columns available to the table.
     *
     * @return Collection
     */
    public function getColumnsProperty()
    {
        return $this->tableBuilder->getColumns();
    }

    /**
     * Return the filters available to the table.
     *
     * @return Collection
     */
    public function getTableFiltersProperty()
    {
        return $this->tableBuilder->getFilters();
    }

    /**
     * Return the actions available to the table.
     *
     * @return Collection
     */
    public function getActionsProperty()
    {
        return $this->tableBuilder->getActions();
    }

    /**
     * Return the bulk actions available.
     *
     * @return void  Collection
     */
    public function getBulkActionsProperty()
    {
        return $this->tableBuilder->getBulkActions();
    }

    /**
     * Return the search placeholder.
     */
    public function getSearchPlaceholderProperty(): string
    {
        return 'Search';
    }

    /**
     * Return the number of active filters.
     *
     * @return int
     */
    public function getActiveFiltersCountProperty()
    {
        return collect($this->filters)->filter()->count();
    }

    /**
     * Return the empty message.
     *
     * @return string|null
     */
    public function getEmptyMessageProperty()
    {
        return $this->tableBuilder->emptyMessage;
    }

    /**
     * {@inheritDoc}
     */
    public function render()
    {
        return view('l-tables::index');
    }
}
