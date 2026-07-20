<?php

namespace Lunar\Core\Contracts\Actions\Media;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

interface DeletesMedia
{
    public function execute(Media $media): void;
}
