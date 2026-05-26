<?php

namespace Lunar\Core\Contracts;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

interface MediaDefinitions
{
    public function registerMediaConversions(HasMedia $model, ?Media $media = null): void;

    public function registerMediaCollections(HasMedia $model): void;

    public function getMediaCollectionTitles(): array;

    public function getMediaCollectionDescriptions(): array;
}
