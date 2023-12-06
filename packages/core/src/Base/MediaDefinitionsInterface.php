<?php

namespace Lunar\Base;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\HasMedia;

interface MediaDefinitionsInterface
{
    public function registerMediaConversions(HasMedia $model, Media $media = null): void;

    public function registerMediaCollections(HasMedia $model): void;
}
