<?php

namespace Lunar\Base\Traits;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Lunar\Base\StandardMediaCollections;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait HasMedia
{
    use InteractsWithMedia;

    /**
     * Relationship for thumbnail.
     */
    public function thumbnail(): MorphOne
    {
        return $this->morphOne(config('media-library.media_model'), 'model')
            ->where('custom_properties->primary', true);
    }

    public function registerMediaCollections(): void
    {
        $conversionClass = config('lunar.media.collections', StandardMediaCollections::class);
        app($conversionClass)->apply($this);
    }

    public function registerMediaConversions(Media $media = null): void
    {
        // Add a conversion for the admin panel to use
        $this->addMediaConversion('small')
            ->fit(Manipulations::FIT_FILL, 300, 300)
            ->sharpen(10)
            ->keepOriginalImageFormat();
    }
}
