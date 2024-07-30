<?php

namespace Lunar\Admin\Support\Synthesizers;

use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;
use Lunar\Base\FieldType;

abstract class AbstractFieldSynth extends Synth
{
    public static $key = 'lunar_field';

    protected static $targetClass = FieldType::class;

    public static function match($target)
    {
        return $target instanceof static::$targetClass;
    }

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
