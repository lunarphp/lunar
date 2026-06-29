<?php

namespace Lunar\Core\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Lunar\Core\Models\Url;

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
            $model->urls()->delete();
        });
    }

    /**
     * Get all of the models urls.
     */
    public function urls(): MorphMany
    {
        return $this->morphMany(
            Url::class,
            'element'
        );
    }

    public function defaultUrl(): MorphOne
    {
        return $this->morphOne(
            Url::class,
            'element'
        )->whereDefault(true);
    }

    public function localeUrl(?string $locale = null): MorphOne
    {
        return $this->morphOne(
            Url::class,
            'element'
        )->whereHas('language', function (Builder $query) use ($locale) {
            $query->where('code', $locale ?: app()->getLocale());
        });
    }
}
