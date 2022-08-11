<?php

namespace GetCandy\Base\Traits;

use Illuminate\Support\Str;
use Spatie\LaravelBlink\BlinkFacade as Blink;

trait HasDefaultRecord
{
    /**
     * Return the default scope.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeDefault($query, $default = true)
    {
        $query->whereDefault($default);
    }

    /**
     * Get the default record.
     *
     * @return self
     */
    public static function getDefault()
    {
        $key = 'getcandy_default_'.Str::snake(self::class);

        return Blink::once($key, function () {
            return self::query()->default(true)->first();
        });
    }
}
