<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

if (! function_exists('max_upload_filesize')) {
    function max_upload_filesize()
    {
        return (int) ini_get('upload_max_filesize') * 1000;
    }
}

if (! function_exists('get_validation')) {
    function get_validation($reference, $field, $defaults = [], Model $model = null)
    {
        $config = config("getcandy-hub.{$reference}.{$field}", []);

        $rules = $defaults;

        $rules[] = ! empty($config['required']) ? 'required' : 'nullable';

        if (($config['unique'] ?? false) && $model) {
            $rule = 'unique:'.$model->getTable().','.$field;

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

        if ($alias) {
            $select .= " as {$alias}";
        }

        return DB::RAW($select);
    }
}
