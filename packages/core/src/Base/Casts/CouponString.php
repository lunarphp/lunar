<?php

namespace Lunar\Base\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Str;

class CouponString implements CastsAttributes
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
        return Str::upper($value);
    }
}
