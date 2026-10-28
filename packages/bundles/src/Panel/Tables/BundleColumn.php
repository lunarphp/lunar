<?php

namespace Lunar\Bundles\Panel\Tables;

use Illuminate\Database\Eloquent\Builder;
use Lunar\Panel\Tables\TableColumn;

/**
 * A "Bundle" badge on products with at least one bundle variant. The value
 * is a boolean from a single `withExists` subquery, rendered by the add-on's
 * `bundles::BundleBadge` cell so ordinary products show an empty cell.
 */
class BundleColumn extends TableColumn
{
    public function key(): string
    {
        return 'is_bundle';
    }

    public function header(): string
    {
        return __('bundles::bundles.panel.column_bundle');
    }

    public function component(): ?string
    {
        return 'bundles::BundleBadge';
    }

    public function query(Builder $query): void
    {
        $query->withExists('bundles as is_bundle');
    }
}
