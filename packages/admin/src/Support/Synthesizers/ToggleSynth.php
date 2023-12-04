<?php

namespace Lunar\Admin\Support\Synthesizers;

use Lunar\FieldTypes\Toggle;

class ToggleSynth extends AbstractFieldSynth
{
    public static $key = 'lunar_toggle_field';

    protected static $targetClass = Toggle::class;

    public function dehydrate($target)
    {
        return [$target->getValue(), []];
    }

    public function hydrate($value)
    {
        $instance = new static::$targetClass;
        $instance->setValue($value);

        return $instance;
    }

    public function get(&$target, $key)
    {
        return $target->{$key};
    }

    public function set(&$target, $key, $value)
    {
        $target->{$key} = $value;
    }
}
