<?php

namespace GetCandy\Base\Traits;

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
        return self::query()->default(true)->first();
    }
}
