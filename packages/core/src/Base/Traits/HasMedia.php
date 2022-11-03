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
        $this->addMediaCollection('images');

        if (config('lunar.media.fallback.url') != null) {
            $this->getMediaCollection('images')->useFallbackUrl(config('lunar.media.fallback.url'));
        }

        if (config('lunar.media.fallback.url') != null) {
            $this->getMediaCollection('images')->useFallbackPath(config('lunar.media.fallback.url'));
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
