<?php

namespace Lunar\Admin\Support\Synthesizers;

use Lunar\FieldTypes\TranslatedText;

class TranslatedTextSynth extends AbstractFieldSynth
{
    public static $key = 'lunar_translatedtext_field';

    protected static $targetClass = TranslatedText::class;

    public function dehydrate($target)
    {
        return [$target->getValue()->toArray(), []];
    }

    public function hydrate($value)
    {
        $instance = new static::$targetClass;
        $instance->setValue(collect($value));

        return $instance;
    }

    public function get(&$target, $key)
    {
        return $target->{$key};
    }

    public function set(&$target, $key, $value)
    {
        $collectionValue = $target->getValue();
        $field = $collectionValue->get($key);

        $field->setValue($value);

        $collectionValue->put($key, $field);

        $target->setValue($collectionValue);
    }
}
