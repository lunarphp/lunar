<?php

namespace GetCandy\Base\Traits;

use Illuminate\Support\Arr;

trait HasTranslations
{
    /**
     * Translate a given attribute based on passed locale.
     *
     * @param  string  $attribute
     * @param  string  $locale
     * @return string
     */
    public function translate($attribute, $locale = null)
    {
        $values = $this->getAttribute($attribute);

        if (is_string($values)) {
            return $values;
        }

        if (! $values) {
            return null;
        }

        $value = Arr::get($values, $locale ?: app()->getLocale());

        return $value ?: Arr::get(
            $values,
            app()->getLocale(),
            Arr::first($values)
        );
    }

    /**
     * Translate a value from attribute data.
     *
     * @param  string  $attribute
     * @param  string  $locale
     * @return string
     */
    public function translateAttribute($attribute, $locale = null)
    {
        $field = Arr::get($this->getAttribute('attribute_data'), $attribute);

        if (! $field) {
            return null;
        }

        $translations = $field->getValue();

        if (is_string($translations) || ! $translations) {
            return $translations;
        }

        $value = Arr::get($translations, $locale ?: app()->getLocale(), Arr::first($translations));

        return $value ? $value->getValue() : null;
    }
}
