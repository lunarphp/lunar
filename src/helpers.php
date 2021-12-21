<?php

use Illuminate\Database\Eloquent\Model;

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
