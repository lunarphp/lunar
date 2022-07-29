<?php

use GetCandy\DataTypes\Price;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

if (! function_exists('max_upload_filesize')) {
    function max_upload_filesize()
    {
        return (int) ini_get('upload_max_filesize') * 1000;
    }
}

if (!function_exists('get_validation')) {
    function get_validation($reference, $field, $defaults = [], Model $model = null): array
    {
        $config = config("getcandy-hub.{$reference}.{$field}", []);

        return evaluate_validation_config($field, $config, $defaults, $model);
    }

    function get_extended_validation($reference): array
    {
        $config = config("getcandy-hub.{$reference}.validation", []);

        return collect($config)
            ->map(fn ($rule) => evaluate_validation_config(null, $rule))
            ->toArray();
    }

    function evaluate_validation_config($field, $config, $defaults = [], Model $model = null): array
    {
        if (blank($config)) {
            return $defaults;
        }

        $rules = $defaults;

        $keyPos = array_search('required', $config, true);
        if ($keyPos !== false) {
            $config['required'] = true;
            unset($config[$keyPos]);
        }


        $rules[] = !empty($config['required']) ? 'required' : 'nullable';

        if (($config['unique'] ?? false) && $model && $field) {
            $rule = 'unique:' . get_class($model) . ',' . $field;

            if ($model->id) {
                $rule .= ',' . $model->id;
            }

            $rules[] = $rule;
        }

        collect($config)
            ->except(['unique', 'required'])
            ->each(function ($rule, $ruleKey) use (&$rules, $model) {
                if (is_bool($rule) && $rule) {
                    $rules[] = $ruleKey;

                    return;
                }

                $rules[] = $rule;
            });

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

        return DB::RAW($select);
    }
}

if (! function_exists('price')) {
    function price($value, $currency, $unitQty = 1)
    {
        return new Price($value, $currency, $unitQty);
    }
}
