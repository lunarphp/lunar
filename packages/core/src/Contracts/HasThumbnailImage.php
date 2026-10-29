<?php

namespace Lunar\Core\Contracts;

interface HasThumbnailImage
{
    /**
     * Return the thumbnail image URL, or null when the record has no thumbnail.
     */
    public function getThumbnailImage(): ?string;
}
