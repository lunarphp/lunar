<?php

namespace Lunar\Panel\Tables;

use Illuminate\Database\Eloquent\Builder;

abstract class TableExtension
{
    /**
     * Extend the keyword search query with additional `orWhere` clauses.
     */
    public function searchQuery(Builder $query, string $term): void {}

    /** @return class-string<TableColumn>[] */
    public function columns(): array
    {
        return [];
    }

    /** @return class-string<TableFilter>[] */
    public function filters(): array
    {
        return [];
    }

    /** @return class-string<TableAction>[] */
    public function actions(): array
    {
        return [];
    }

    /** @return class-string<TableBulkAction>[] */
    public function bulkActions(): array
    {
        return [];
    }
}
