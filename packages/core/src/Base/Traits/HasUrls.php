<?php

namespace GetCandy\Base\Traits;

use GetCandy\Models\Url;

trait HasUrls
{
    /**
     * Get all of the models urls.
     */
    public function urls()
    {
        return $this->morphMany(
            Url::class,
            'element'
        );
    }

    public function defaultUrl()
    {
        return $this->morphOne(
            Url::class,
            'element'
        )->whereDefault(true);
    }
}
