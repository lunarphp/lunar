<?php

namespace GetCandy\Hub\Tables;

use Filament\Tables\Columns\Column;
use Filament\Tables\Filters\BaseFilter;
use Filament\Support\Actions\Action;
use Filament\Tables\Actions\BulkAction;

class TableExtension
{
    /**
     * The classname for the table we want to extend.
     *
     * @var string
     */
    protected $table = null;

    /**
     * The columns to add to the table.
     *
     * @var array
     */
    protected array $columns = [];

    /**
     * The filters to add to the table.
     *
     * @var array
     */
    protected array $filters = [];

    /**
     * The actions to add to the table.
     *
     * @var array
     */
    protected array $actions = [];

    /**
     * The bulk actions to add to the table.
     *
     * @var array
     */
    protected array $bulkActions = [];

    /**
     * Initialise the extension
     *
     * @param string $tableName
     */
    public function __construct($tableName)
    {
        $this->table = $tableName;
    }

    /**
     * Return the classname of the table we want to extend.
     *
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Add a column to the table
     *
     * @param Column $column
     * @param type $after
     *
     * @return self
     */
    public function addColumn(Column $column, $after = null): self
    {
        $this->columns[] = [
            'column' => $column,
            'after' => $after,
        ];

        return $this;
    }

    /**
     * Return the extensions table columns.
     *
     * @return array
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Add a new filter to the table.
     *
     * @param BaseFilter $filter
     *
     * @return self
     */
    public function addFilter(BaseFilter $filter): self
    {
        $this->filters[] = $filter;

        return $this;
    }

    /**
     * Return the extensions table filters.
     *
     * @return array
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * Add a new action to the table.
     *
     * @param Action $action
     *
     * @return self
     */
    public function addAction(Action $action): self
    {
        $this->actions[] = $action;

        return $this;
    }

    /**
     * return the extensions actions.
     *
     * @return array
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * Add a new action to the table.
     *
     * @param Action $action
     *
     * @return self
     */
    public function addBulkAction(BulkAction $action): self
    {
        $this->bulkActions[] = $action;

        return $this;
    }

    /**
     * return the extensions actions.
     *
     * @return array
     */
    public function getBulkActions(): array
    {
        return $this->bulkActions;
    }
}
