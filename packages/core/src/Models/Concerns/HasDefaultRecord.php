<?php

namespace Lunar\Core\Models\Concerns;

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
     * Null when nothing is marked as default — an install that has not been
     * seeded, or a record that was unset.
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
