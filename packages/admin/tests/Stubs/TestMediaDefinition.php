<?php

namespace Lunar\Admin\Tests\Stubs;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TestMediaDefinition implements \Lunar\Base\MediaDefinitionsInterface
{
    public function registerMediaConversions(HasMedia $model, Media $media = null): void
    {

    }

    public function registerMediaCollections(HasMedia $model): void
    {
        $model->addMediaCollection('images');
        $model->addMediaCollection('videos');
    }

    public function getMediaCollectionTitles(): array
    {
        return [
            'images' => 'Images',
            'videos' => 'Videos',
        ];
    }

    public function getMediaCollectionDescriptions(): array
    {
        return [
            'images' => 'Images',
            'videos' => 'Videos',
        ];
    }
}
