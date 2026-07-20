<?php

namespace Lunar\Core\Actions\Media;

use Lunar\Core\Contracts\Actions\Media\DeletesMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Delete a media item. When it was the primary, MediaObserver promotes the
 * first remaining media in the collection.
 */
class DeleteMedia implements DeletesMedia
{
    public function execute(Media $media): void
    {
        $media->delete();
    }
}
