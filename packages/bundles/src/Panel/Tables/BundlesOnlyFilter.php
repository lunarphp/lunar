<?php

namespace Lunar\Bundles\Panel\Tables;

use Illuminate\Database\Eloquent\Builder;
use Lunar\Panel\Tables\TableFilter;

class BundlesOnlyFilter extends TableFilter
{
    public function key(): string
    {
        return 'bundles';
    }

    public function label(): string
    {
        return __('bundles::bundles.panel.filter_label');
    }

    /** @return array<string, string> */
    public function options(): array
    {
        return [
            'only' => __('bundles::bundles.panel.filter_only'),
        ];
    }

    public function query(Builder $query, mixed $value): void
    {
        if ($value === 'only') {
            $query->whereHas('bundles');
        }
    }
}
