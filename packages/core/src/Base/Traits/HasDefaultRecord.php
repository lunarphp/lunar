<?php

namespace Lunar\Base\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\LaravelBlink\BlinkFacade as Blink;

trait HasDefaultRecord
{

    /** 
     * Method when trait is booted
     *
     * @return void
     */
    public static function bootedHasDefaultRecord(): void
    {
        static::saved(function (Model $model) {
            if ($model->isDirty('default') && $model->default) {
                $model->newModelQuery()
                    ->default()
                    ->where($model->getKeyName(), '!=', $model->getKey())
                    ->update([
                        'default' => false
                    ]);
            }
        });
    }

    /**
     * Method when trait is initialized.
     *
     * @return void
     */
    public function initializeHasDefaultRecord(): void
    {
        $this->mergeCasts([
            'default' => 'boolean',
        ]);
        $this->attributes['default'] = false;
    }

    /**
     * Return the default scope.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeDefault($query, $default = true): void
    {
        $query->whereDefault($default);
    }

    /**
     * Get the default record.
     *
     * @return self
     */
    public static function getDefault(): ?self
    {
        $key = 'lunar_default_'.Str::snake(self::class);

        $value = Blink::once($key, function () {
            return self::query()->default()->first();
        });

        // Don't cache if default model is present
        if (is_null($value)) {
            Blink::forget($key);
        }

        return $value;
    }
}
