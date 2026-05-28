<?php

namespace Lunar\Core\Models\Concerns;

use Illuminate\Support\Arr;

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
}
