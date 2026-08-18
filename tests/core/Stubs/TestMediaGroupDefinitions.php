<?php

namespace Lunar\Tests\Core\Stubs;

use Lunar\Core\Contracts\MediaDefinitions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Registers the image collection alongside a non-image "downloads" collection,
 * exercising the multi-collection media-group path (spec 0060).
 */
class TestMediaGroupDefinitions implements MediaDefinitions
{
    public function registerMediaConversions(HasMedia $model, ?Media $media = null): void {}

    public function registerMediaCollections(HasMedia $model): void
    {
        $model->addMediaCollection(config('lunar.media.collection'))
            ->acceptsMimeTypes(['image/jpeg', 'image/png']);

        $model->addMediaCollection('downloads')
            ->acceptsMimeTypes(['application/pdf']);
    }

    public function getMediaCollectionTitles(): array
    {
        return [
            config('lunar.media.collection') => 'Images',
            'downloads' => 'Downloads',
        ];
    }

    public function getMediaCollectionDescriptions(): array
    {
        return [
            config('lunar.media.collection') => '',
            'downloads' => 'Downloadable files',
        ];
    }
}
