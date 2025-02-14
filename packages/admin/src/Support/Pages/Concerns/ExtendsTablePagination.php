<?php

namespace Lunar\Admin\Support\Pages\Concerns;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;

trait ExtendsTablePagination
{
    protected function getDefaultPaginationQuery(Builder $query): Paginator|CursorPaginator
    {
        return parent::paginateTableQuery($query);
    }

    protected function paginateTableQuery(Builder $query): Paginator|CursorPaginator
    {
        $query = $this->callLunarHook('paginateTableQuery', $query, $this->getTableRecordsPerPage());

        return $query instanceof Builder ? $this->getDefaultPaginationQuery($query) : $query;
    }
}
