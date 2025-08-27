<?php

namespace Lunar\Tests\Admin\Stubs\Support\Synthesizers;

use Illuminate\Support\Arr;
use Lunar\Admin\Support\Synthesizers\AbstractFieldSynth;
use Lunar\Tests\Admin\Stubs\FieldTypes\BuilderField as CoreBuilderField;

class BuilderSynth extends AbstractFieldSynth
{
    public static $key = 'lunar_builder_field';

    protected static $targetClass = CoreBuilderField::class;

    public function get(&$target, $key)
    {
        $value = (array) $target->getValue();

        if ($key === '' || $key === null) {
            return $this->normalize($value);
        }

        return Arr::get($value, $key);
    }

    public function set(&$target, $key, $value)
    {
        if ($key === '' || $key === null) {
            // Let the FieldType perform full normalization.
            $target->setValue(is_array($value) ? $value : []);

            return;
        }

        $fieldValue = (array) $target->getValue();
        Arr::set($fieldValue, $key, $value);

        // Delegate normalization to the FieldType implementation.
        $target->setValue($fieldValue);
    }

    public function unset(&$target, $index)
    {
        $fieldValue = (array) $target->getValue();

        Arr::forget($fieldValue, $index);

        // Delegate normalization to the FieldType implementation.
        $target->setValue($fieldValue);
    }

    protected function normalize(array $items): array
    {
        $items = array_values(array_filter($items, 'is_array'));

        return $items;
    }
}
