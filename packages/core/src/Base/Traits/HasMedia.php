<?php

namespace Lunar\Base\Traits;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Lunar\Base\StandardMediaDefinitions;
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
        app($this->getDefinitionClass())->registerMediaCollections($this);
    }

    public function registerMediaConversions(Media $media = null): void
    {
        app($this->getDefinitionClass())->registerMediaConversions($this, $media);
    }

    protected function getDefinitionClass()
    {
        $conversionClasses = config('lunar.media.definitions', []);

        return $conversionClasses[static::class] ?? StandardMediaDefinitions::class;
    }
}
