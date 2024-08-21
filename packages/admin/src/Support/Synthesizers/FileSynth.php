<?php

namespace Lunar\Admin\Support\Synthesizers;

use Illuminate\Support\Arr;
use Lunar\FieldTypes\File;

class FileSynth extends AbstractFieldSynth
{
    public static $key = 'lunar_file_field';

    protected static $targetClass = File::class;

    public function dehydrate($target)
    {
        return [$target->getValue(), []];
    }

    public function hydrate($value)
    {
        $instance = new static::$targetClass;

        $instance->setValue($value);

        return Arr::wrap($value);
    }

    public function get(&$target, $key)
    {
        return $target->{$key};
    }

    public function set(&$target, $key, $value)
    {
        $files = collect($target->getValue() ?? []);
        $files->put($key, $value);
        $target->setValue($files);
    }
}
