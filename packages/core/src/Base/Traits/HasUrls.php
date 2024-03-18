<?php

namespace Lunar\Base\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Lunar\Models\Url;

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
            $generator = config('lunar.urls.generator', null);
            if ($generator) {
                app($generator)->handle($model);
            }
        });

        static::deleted(function (Model $model) {
            if (! $model->deleted_at) {
                $model->urls()->delete();
            }
        });
    }

    /**
     * Get all of the models urls.
     */
    public function urls(): MorphMany
    {
        return $this->morphMany(
            Url::modelClass(),
            'element'
        );
    }

    public function defaultUrl(): MorphOne
    {
        return $this->morphOne(
            Url::modelClass(),
            'element'
        )->whereDefault(true);
    }
}
