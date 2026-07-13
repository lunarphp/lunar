<?php

namespace Lunar\Panel\Http\Controllers\Concerns;

use Lunar\Panel\Facades\Panel;
use Lunar\Panel\Tables\Resolvers\TableExtensionResolver;

/**
 * Shared "resolve a table's extensions and shape its Inertia props" sequence,
 * used by every table-backed index page so column/action extension is a
 * cross-cutting panel feature rather than a per-controller reimplementation.
 */
trait ResolvesTableExtensions
{
    protected function resolveTable(string $tableId): TableExtensionResolver
    {
        return Panel::resolveExtensions($tableId);
    }

    /**
     * The Inertia props every table page shares: the merged, ordered columns
     * plus the static row-action and bulk-action descriptors. Per-row action
     * URLs are resolved separately via $resolver->resolveRowActionUrls($record)
     * while building each row.
     *
     * @param  array<int, array{key: string, label: string, width?: string, align?: string}>  $firstPartyColumns
     * @return array{columns: array<int, array<string, mixed>>, tableActions: array<int, array<string, mixed>>, tableBulkActions: array<int, array<string, mixed>>}
     */
    protected function tableProps(TableExtensionResolver $resolver, array $firstPartyColumns): array
    {
        return [
            'columns' => $resolver->mergeAndOrderColumns($firstPartyColumns),
            'tableActions' => $resolver->getActions(),
            'tableBulkActions' => $resolver->getBulkActions(),
        ];
    }
}
