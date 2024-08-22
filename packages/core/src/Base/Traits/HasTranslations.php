<?php

namespace Lunar\Base\Traits;

use Illuminate\Support\Arr;
use Lunar\Base\FieldType;

trait HasTranslations
{
    /**
     * Translate a given attribute based on passed locale.
     *
     * @param  string  $attribute
     * @param  string  $locale
     * @return string|null
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

        $locale = $locale ?: app()->getLocale();

        $value = Arr::accessible($values) ?
            Arr::get($values, $locale) :
            get_object_vars($values)[$locale] ?? null;

        return $value ?: Arr::get(
            $values,
            app()->getLocale(),
            Arr::first($values)
        );
    }

    /**
     * Translate a value from attribute data.
     */
    public function translateAttribute(string $attribute, ?string $locale = null): mixed
    {
        $field = Arr::get($this->getAttribute('attribute_data'), $attribute);

        if (! $field) {
            return null;
        }

        $translations = $field->getValue();

        if (! is_iterable($translations) || ! $translations) {
            return $translations;
        }

        $value = Arr::get($translations, $locale ?: app()->getLocale(), Arr::first($translations));

        // When we don't have a value, we just return null as it may not have a value.
        if (! $value) {
            return null;
        }

        /**
         * If we don't return a field type, then somethings up and it doesn't look like
         * this is translatable, in this case, just return what the fields value is.
         */
        if (! $value instanceof FieldType) {
            return $field->getValue();
        }

        if (! $value->getValue()) {
            return $translations->first(
                fn ($value) => $value->getValue()
            )?->getValue();
        }

        return $value->getValue();
    }

    /**
     * Shorthand to translate an attribute.
     *
     * @return string|null
     */
    public function attr(...$params): mixed
    {
        return $this->translateAttribute(...$params);
    }
}
