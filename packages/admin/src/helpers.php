<?php

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Models\Address;
use Lunar\Core\Models\Concerns\Searchable;
use Lunar\Core\Models\ProductVariant;
use Lunar\Filament\Forms\Components\Support\RecordSearch;

if (! function_exists('price')) {
    function price($value, $currency)
    {
        return new PriceValue((int) $value, $currency);
    }
}

if (! function_exists('sync_with_search')) {
    function sync_with_search(?Model $model = null): void
    {
        if (! $model) {
            return;
        }

        $isSearchable = in_array(Searchable::class, class_uses_recursive($model));

        if ($isSearchable) {
            $model->searchable();

            return;
        }

        if ($model instanceof ProductVariant) {
            $model->product()->first()->searchable();
        }

        if ($model instanceof Address) {
            $model->customer()->first()->searchable();
        }

        if (is_lunar_user($model)) {
            foreach ($model->customers()->get() as $customer) {
                $customer->searchable();
            }
        }
    }
}

if (! function_exists('db_date')) {
    function db_date($column, $format, $alias = null)
    {
        $connection = config('database.default');

        $driver = config("database.connections.{$connection}.driver");

        $select = "DATE_FORMAT({$column}, '{$format}')";

        if ($driver == 'pgsql') {
            $format = str_replace('%', '', $format);
            $select = "TO_CHAR({$column} :: DATE, '{$format}')";
        }

        if ($driver == 'sqlite') {
            $select = "strftime('{$format}', {$column})";
        }

        if ($alias) {
            $select .= " as {$alias}";
        }

        return $select;
    }
}

if (! function_exists('get_search_builder')) {

    /**
     * @deprecated Use `\Lunar\Filament\Forms\Components\Support\RecordSearch::for()` instead. Removal targeted for v3.
     */
    function get_search_builder(string $model, string $search): Laravel\Scout\Builder|Builder
    {
        return RecordSearch::for($model, $search);
    }
}
