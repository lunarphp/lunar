<?php

namespace Lunar\Base\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Spatie\LaravelBlink\BlinkFacade as Blink;

trait HasDefaultRecord
{
    /**
     * Return the default scope.
     *
     * @param  Builder  $query
     * @return void
     */
    public function scopeDefault($query, $default = true)
    {
        $query->whereDefault($default);
    }

    /**
     * Get the default record.
     *
     * @return null|self
     */
    public static function getDefault()
    {
        $key = 'lunar_default_'.Str::snake(self::class);

        return Blink::once($key, function () {
            return self::query()->default(true)->first();
        });
    }
}
