<?php

use Lunar\Base\Traits\Searchable;
use Lunar\DataTypes\Price;

if (! function_exists('price')) {
    function price($value, $currency, $unitQty = 1)
    {
        return new Price($value, $currency, $unitQty);
    }
}

if (! function_exists('sync_with_search')) {
    function sync_with_search(Illuminate\Database\Eloquent\Model $model = null): void
    {
        if (! $model) {
            return;
        }

        $isSearchable = in_array(Searchable::class, class_uses($model));

        if ($isSearchable) {
            $model->searchable();

            return;
        }

        if ($model instanceof \Lunar\Models\ProductVariant) {
            $model->product()->first()->searchable();
        }

        if ($model instanceof \Lunar\Models\Address) {
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
