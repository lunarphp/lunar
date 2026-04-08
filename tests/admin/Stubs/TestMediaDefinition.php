<?php

namespace Lunar\Tests\Admin\Stubs;

use Lunar\Base\MediaDefinitionsInterface;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TestMediaDefinition implements MediaDefinitionsInterface
{
    public function registerMediaConversions(HasMedia $model, ?Media $media = null): void {}

    public function registerMediaCollections(HasMedia $model): void
    {
        $model->addMediaCollection(config('lunar.media.collection'));
        $model->addMediaCollection('videos');
    }

    public function getMediaCollectionTitles(): array
    {
        return [
            config('lunar.media.collection') => 'Images',
            'videos' => 'Videos',
        ];
    }

    public function getMediaCollectionDescriptions(): array
    {
        return [
            config('lunar.media.collection') => 'Images',
            'videos' => 'Videos',
        ];
    }
}
