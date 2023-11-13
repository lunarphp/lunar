<?php

namespace Lunar\Base\Casts;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Collection;
use Lunar\Models\Language;

class AsTranslated implements Castable
{
    /**
     * Get the caster class to use when casting from / to this cast target.
     *
     * @param  array  $arguments
     * @return \Illuminate\Contracts\Database\Eloquent\CastsAttributes<\Illuminate\Support\Collection<array-key, mixed>, iterable>
     */
    public static function castUsing(array $arguments)
    {
        return new class($arguments) implements CastsAttributes
        {
            public function __construct(protected array $arguments)
            {
            }

            public function get($model, $key, $value, $attributes)
            {
                $value = json_decode($value, true);

                if (is_array($value)) {
                    return collect($value);
                }

                return $this->fromLocale($value);
            }

            public function set($model, $key, $value, $attributes)
            {
                if (is_array($value)) {
                    $value = collect($value);
                }

                if (! $value instanceof Collection) {
                    $value = $this->fromLocale($value);
                }

                return [$key => json_encode($value)];
            }

            /**
             * Create a collection with the default, first or current locale 
             * as key with the given value
             * 
             * @param mixed $value
             * @return \Illuminate\Support\Collection
             */
            private function fromLocale(mixed $value): Collection
            {
                $locale = Language::getDefault()?->code 
                    ?? Language::first() 
                    ?? app()->getLocale();
                return collect([$locale => $value]);
            }
        };
    }
}