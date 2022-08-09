<?php

namespace GetCandy\Hub\Tables;

use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use GetCandy\Hub\Tables\Columns\AttributeColumn;
use GetCandy\Hub\Tables\Columns\ThumbnailColumn;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

abstract class GetCandyTable extends Component implements HasTable
{
    use InteractsWithTable;

    /**
     * The extra columns to add to the table.
     *
     * @var array
     */
    public static $extraColumns = [];

    /**
     * The extra filters to add to the table.
     *
     * @var array
     */
    public static $extraFilters = [];

    /**
     * The extra actions to add to the table.
     *
     * @var array
     */
    public static $extraActions = [];

    /**
     * The extra bulk actions to add to the table.
     *
     * @var array
     */
    public static $extraBulkActions = [];

    /**
     * The table columns to use instead of the default.
     *
     * @var array
     */
    public static $columnsOverride = null;

    /**
     * {@inerhitDoc}.
     */
    protected $queryString = [
        'tableFilters',
        'tableSearchQuery' => ['except' => '', 'as' => 'query'],
    ];

    /**
     * Add a column to the table.
     *
     * @param  Column|array  $column
     * @return void
     */
    final public static function addColumn($column, $position = null)
    {
        if (! is_array($column)) {
            $column = [$column];
        }

        self::$extraColumns[] = [
            'position' => $position,
            'column' => $column,
        ];
    }

    /**
     * Add a filter to the table.
     *
     * @param  Filter|array  $filter
     * @return void
     */
    final public static function addFilter($filter)
    {
        if (! is_array($filter)) {
            $filter = [$filter];
        }

        self::$extraFilters = array_merge(self::$extraFilters, $filter);
    }

    /**
     * Add a filter to the table.
     *
     * @param  Filter|array  $filter
     * @return void
     */
    final public static function addAction($action)
    {
        if (! is_array($action)) {
            $action = [$action];
        }

        self::$extraActions = array_merge(self::$extraActions, $action);
    }

    /**
     * Add a filter to the table.
     *
     * @param  Filter|array  $filter
     * @return void
     */
    final public static function addBulkAction($bulkAction)
    {
        if (! is_array($bulkAction)) {
            $bulkAction = [$bulkAction];
        }

        self::$extraActions = array_merge(self::$extraBulkActions, $bulkAction);
    }

    /**
     * Explicitly set the columns available.
     *
     * @param  array  $columns
     * @return void
     */
    final public static function setColumns(array $columns)
    {
        self::$columnsOverride = $columns;
    }

    /**
     * {@inhertDoc}.
     */
    protected function getTableFilters(): array
    {
        return array_merge(
            $this->getBaseTableFilters(),
            self::$extraFilters
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function getTableActions(): array
    {
        return array_merge(
            $this->getBaseTableActions(),
            self::$extraActions
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function getTableBulkActions(): array
    {
        return array_merge(
            $this->getBaseTableBulkActions(),
            self::$extraBulkActions
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getTableColumns(): array
    {
        if (self::$columnsOverride) {
            return self::$columnsOverride;
        }

        $columns = collect($this->getBaseTableColumns());

        $extraColumns = static::$extraColumns;

        foreach ($extraColumns as $column) {
            if (! $position = $column['position']) {
                $columns = $columns->merge($column['column']);
                continue;
            }

            $index = $columns->search(function ($column) use ($position) {
                return $column->getName() == $position;
            });

            if ($index === false) {
                continue;
            }

            $columns->splice($index + 1, 0, $column['column']);
        }

        return $columns->values()->toArray();
    }

    /**
     * Return a column for status.
     *
     * @param  string  $column
     * @return BadgeColumn
     */
    public function statusColumn($column = 'status')
    {
        return BadgeColumn::make($column)
        ->enum([
            'unpublished' => __('adminhub::tables.unpublished'),
            'published' => __('adminhub::tables.published'),
        ])->colors([
            'danger' => 'unpublished',
            'success' => 'published',
        ]);
    }

    /**
     * Return a status filter for the table.
     *
     * @param  string  $column
     * @return SelectFilter
     */
    public function statusFilter($column = 'status')
    {
        return SelectFilter::make($column)
            ->options([
                'published' => __('adminhub::tables.published'),
                'unpublished' => __('adminhub::tables.unpublished'),
            ]);
    }

    public function trashedFilter()
    {
        return TernaryFilter::make('trashed')
        ->placeholder(
            __('adminhub::tables.without_trashed')
        )
        ->trueLabel(
            __('adminhub::tables.with_trashed')
        )
        ->falseLabel(
            __('adminhub::tables.only_trashed')
        )
        ->queries(
            true: fn (Builder $query) => $query->withTrashed(),
            false: fn (Builder $query) => $query->onlyTrashed(),
            blank: fn (Builder $query) => $query->withoutTrashed(),
        );
    }

    protected function exportUsing($classname, $ids)
    {
        return app($classname)->export($ids);
    }

    /**
     * Return a column for thumbnail.
     *
     * @param  string  $column
     * @return ThumbnailColumn
     */
    public function thumbnailColumn($column = 'thumbnail')
    {
        return ThumbnailColumn::make($column)->label('Thumbnail');
    }

    /**
     * Return a column for an attribute.
     *
     * @param  string  $attribute
     * @return AttributeColumn
     */
    public function attributeColumn($attribute)
    {
        return AttributeColumn::make($attribute);
    }

    /**
     * {@inheritDoc}
     */
    abstract protected function getTableQuery(): Builder;

    /**
     * {@inheritDoc}
     */
    protected function applySearchToTableQuery(Builder $query): Builder
    {
        return $query;
    }

    /**
     * Return the base filters GetCandy requires.
     *
     * @return array
     */
    abstract protected function getBaseTableFilters(): array;

    /**
     * Return the base columns GetCandy requires.
     *
     * @return array
     */
    abstract protected function getBaseTableColumns(): array;

    /**
     * Return the base actions GetCandy requires.
     *
     * @return array
     */
    abstract protected function getBaseTableActions(): array;

    /**
     * Return the base bulk actions GetCandy requires.
     *
     * @return array
     */
    abstract protected function getBaseTableBulkActions(): array;
}
