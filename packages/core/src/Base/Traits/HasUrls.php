<?php

namespace GetCandy\Base\Traits;

use GetCandy\Models\Url;
use Illuminate\Database\Eloquent\Model;

trait HasUrls
{
    /**
     * Boot the trait.
     *
     * @return void
     */
    public static function bootHasUrls()
    {
        static::created(function (Model $model) {
            $generator = config('getcandy.urls.generator', null);
            if ($generator) {
                app($generator)->handle($model);
            }
        });
    }

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
