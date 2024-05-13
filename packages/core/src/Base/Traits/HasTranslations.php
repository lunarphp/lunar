<?php

namespace Lunar\Base\Traits;

use Illuminate\Support\Arr;
use Lunar\Base\FieldType;
use Lunar\Models\Language;

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
    public function translateAttribute(string $attribute, string $locale = null): ?string
    {
        $field = Arr::get($this->getAttribute('attribute_data'), $attribute);

        if (! $field) {
            return null;
        }

        $appLocale = app()->getLocale();
        $defaultLocale = Language::getDefault()?->code;

        // If a locale isn't explicitly passed then determine whether
        // we are on a different locale in the app to our default
        // language and if so use that.
        if (! $locale) {
            $locale = $appLocale != $defaultLocale ? $appLocale : $defaultLocale;
        }

        $translations = $field->getValue();

        if (! is_iterable($translations) || ! $translations) {
            return $translations;
        }

        //        dd($translations, $field);

        // Filter out any translations which don't actually have a value.
        // We also sort by whether the translation is for the default locale
        // so in the event we fall back to the first translation, it should
        // be the default language.
        $translations = $translations->reject(
            fn ($fieldType) => ! $fieldType->getValue()
        )->sortBy(
            fn ($fieldType, $key) => $key == $defaultLocale
        );

        $value = Arr::get($translations, $locale, Arr::first($translations));

        dd($value);
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

        return $value?->getValue();
    }

    /**
     * Shorthand to translate an attribute.
     *
     * @return string|null
     */
    public function attr(...$params)
    {
        return $this->translateAttribute(...$params);
    }
}
