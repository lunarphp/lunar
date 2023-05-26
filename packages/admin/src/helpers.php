<?php

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Lunar\Facades\DB;
use Lunar\DataTypes\Price;

if (! function_exists('max_upload_filesize')) {
    function max_upload_filesize()
    {
        return (int) ini_get('upload_max_filesize') * 1000;
    }
}

if (! function_exists('get_validation')) {
    function get_validation($reference, $field, $defaults = [], Model $model = null)
    {
        $config = config("lunar-hub.{$reference}.{$field}", []);

        $rules = $defaults;

        $rules[] = ! empty($config['required']) ? 'required' : 'nullable';

        if (($config['unique'] ?? false) && $model) {
            $rule = 'unique:'.get_class($model).','.$field;

            if ($model->id) {
                $rule .= ','.$model->id;
            }

            $rules[] = $rule;
        }

        return $rules;
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

if (! function_exists('price')) {
    function price($value, $currency, $unitQty = 1)
    {
        return new Price($value, $currency, $unitQty);
    }
}

if (! function_exists('impersonate_link')) {
    function impersonate_link(Authenticatable $authenticatable)
    {
        $class = config('lunar-hub.customers.impersonate');

        if (! $class) {
            return null;
        }

        return app($class)->getUrl($authenticatable);
    }
}

if (! function_exists('lang')) {
    function lang($key, $replace = [], $locale = null, $prefix = 'adminhub::', $lower = false)
    {
        $key = $prefix.$key;

        $value = __($key, $replace, $locale);

        return $lower ? mb_strtolower($value) : $value;
    }
}
