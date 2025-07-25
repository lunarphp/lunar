<?php

namespace Lunar\Base\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Str;

class UppercaseAscii implements CastsAttributes
{
    public function get($model, $key, $value, $attributes): ?string
    {
        return $value ? $this->makeValue($value) : null;
    }

    public function set($model, $key, $value, $attributes): ?string
    {
        return $value ? $this->makeValue($value) : null;
    }

    protected function makeValue(string $value): string
    {
        $value = transliterator_transliterate('Any-Latin; Latin-ASCII; [\u0080-\uffff] Remove', $value);
        $value = str_replace(["'", '’', '‘', '`'], '', $value);
        $value = str_replace(['-'], '_', $value);
        $value = Str::snake($value);

        return Str::upper($value);
    }
}
