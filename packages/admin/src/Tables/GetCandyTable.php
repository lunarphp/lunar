<?php

namespace GetCandy\Hub\Tables;

use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use GetCandy\Hub\Facades\Table;
use GetCandy\Hub\Tables\Columns\AttributeColumn;
use GetCandy\Hub\Tables\Columns\ThumbnailColumn;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

abstract class GetCandyTable extends Component implements HasTable
{
    use InteractsWithTable;

    /**
     * {@inerhitDoc}.
     */
    protected $queryString = [
        'tableFilters',
        'tableSearchQuery' => ['except' => '', 'as' => 'query'],
    ];

    /**
     * {@inhertDoc}.
     */
    protected function getTableFilters(): array
    {
        $extensions = Table::getExtensions(static::class);

        $extraFilters = [];

        foreach ($extensions as $extension) {
            $extraFilters = array_merge(
                $extension->getFilters(),
                $extraFilters
            );
        }

        return array_merge(
            $this->getBaseTableFilters(),
            $extraFilters
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function getTableActions(): array
    {
        $extensions = Table::getExtensions(static::class);

        $extraActions = [];

        foreach ($extensions as $extension) {
            $extraActions = array_merge(
                $extension->getActions(),
                $extraActions
            );
        }

        return array_merge(
            $this->getBaseTableActions(),
            $extraActions
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function getTableBulkActions(): array
    {
        $extensions = Table::getExtensions(static::class);

        $extraBulkActions = [];

        foreach ($extensions as $extension) {
            $extraBulkActions = array_merge(
                $extension->getActions(),
                $extraBulkActions
            );
        }

        return array_merge(
            $this->getBaseTableBulkActions(),
            $extraBulkActions
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getTableColumns(): array
    {
        $extensions = Table::getExtensions(static::class);

        $columns = collect($this->getBaseTableColumns());

        foreach ($extensions as $extension) {
            foreach ($extension->getColumns() as $column) {
                if (! $after = $column['after']) {
                    $columns = $columns->merge([$column['column']]);
                    continue;
                }

                $index = $columns->search(function ($column) use ($after) {
                    return $column->getName() == $after;
                });

                if ($index === false) {
                    continue;
                }

                $columns->splice($index + 1, 0, $column['column']);
            }
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
