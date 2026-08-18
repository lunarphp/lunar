<?php

namespace Lunar\Core\Contracts\Actions\Media;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

interface UpdatesMedia
{
    /**
     * @param  array{name?: string, alt?: ?string, caption?: ?string, focal?: array{x: int, y: int}, primary?: bool}  $properties
     */
    public function execute(Media $media, array $properties): Media;
}
