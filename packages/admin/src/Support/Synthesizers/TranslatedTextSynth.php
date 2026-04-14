<?php

namespace Lunar\Admin\Support\Synthesizers;

use Lunar\FieldTypes\Text;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Models\Language;
use Tiptap\Editor;

class TranslatedTextSynth extends AbstractFieldSynth
{
    public static $key = 'lunar_translatedtext_field';

    protected static $targetClass = TranslatedText::class;

    public function dehydrate($target)
    {
        $languages = Language::orderBy('default', 'desc')->get();

        return [
            $languages->mapWithKeys(
                fn ($language) => [$language->code => $target->getValue()->get($language->code)?->getValue() ?? ''],
            )->toArray(),
            [],
        ];
    }

    public function hydrate($value)
    {
        $instance = new static::$targetClass;
        $instance->setValue(collect($value));

        return $instance;
    }

    public function get(&$target, $key)
    {
        return $target->getValue()->get($key)?->getValue() ?? '';
    }

    public function set(&$target, $key, $value)
    {
        if (is_array($value)) {
            $value = (new Editor)->setContent($value)->getHTML();
        }

        $collectionValue = $target->getValue();
        $field = $collectionValue->get($key);

        if (! $field instanceof Text) {
            $field = new Text;
        }

        $field->setValue($value);

        $collectionValue->put($key, $field);

        $target->setValue($collectionValue);
    }
}
