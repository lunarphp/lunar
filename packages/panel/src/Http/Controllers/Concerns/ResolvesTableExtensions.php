<?php

namespace Lunar\Panel\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Lunar\Panel\Facades\Panel;
use Lunar\Panel\Tables\Resolvers\TableExtensionResolver;

/**
 * Shared "resolve a table's extensions and shape its Inertia props" sequence,
 * used by every table-backed index page so column/action/filter extension is a
 * cross-cutting panel feature rather than a per-controller reimplementation.
 */
trait ResolvesTableExtensions
{
    protected function resolveTable(string $tableId): TableExtensionResolver
    {
        return Panel::resolveExtensions($tableId);
    }

    /**
     * The Inertia props every table page shares: the merged, ordered columns,
     * the static row-action and bulk-action descriptors, and the extension
     * filter definitions with their current request values. Per-row action
     * URLs are resolved separately via $resolver->resolveRowActionUrls($record)
     * while building each row; the controller applies the filters to its query
     * via $resolver->applyFilters($query, $request).
     *
     * @param  array<int, array{key: string, label: string, width?: string, align?: string}>  $firstPartyColumns
     * @return array{columns: array<int, array<string, mixed>>, tableActions: array<int, array<string, mixed>>, tableBulkActions: array<int, array<string, mixed>>, tableFilters: array<int, array<string, mixed>>, tableFilterValues: array<string, mixed>}
     */
    protected function tableProps(TableExtensionResolver $resolver, array $firstPartyColumns, Request $request): array
    {
        return [
            'columns' => $resolver->mergeAndOrderColumns($firstPartyColumns),
            'tableActions' => $resolver->getActions(),
            'tableBulkActions' => $resolver->getBulkActions(),
            'tableFilters' => $resolver->getFilters(),
            'tableFilterValues' => $resolver->getFilterValues($request),
        ];
    }
}
