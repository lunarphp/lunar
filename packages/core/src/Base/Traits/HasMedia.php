<?php

namespace Lunar\Base\Traits;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait HasMedia
{
    use InteractsWithMedia;

    /**
     * Relationship for thumbnail.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function thumbnail(): MorphOne
    {
        return $this->morphOne(config('media-library.media_model'), 'model')
            ->where('custom_properties->primary', true);
    }

    public function registerMediaCollections(): void
    {
        $fallbackUrl = config('lunar.media.fallback.url');
        $fallbackPath = config('lunar.media.fallback.path');

        if ($fallbackUrl != null && $fallbackPath != null) {
            $this->addMediaCollection('images')
                ->useFallbackUrl($fallbackUrl)
                ->useFallbackPath($fallbackPath);
        }

        if ($fallbackUrl != null && $fallbackPath == null) {
            $this->addMediaCollection('images')
                ->useFallbackUrl($fallbackUrl);
        }

        if ($fallbackUrl == null && $fallbackPath != null) {
            $this->addMediaCollection('images')
                ->useFallbackPath($fallbackPath);
        }

    }

    public function registerMediaConversions(Media $media = null): void
    {
        $conversionClasses = config('lunar.media.conversions', []);

        foreach ($conversionClasses as $classname) {
            app($classname)->apply($this);
        }

        // Add a conversion that the hub uses...
        $this->addMediaConversion('small')
            ->fit(Manipulations::FIT_FILL, 300, 300)
            ->sharpen(10)
            ->keepOriginalImageFormat();
    }
}
