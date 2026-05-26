<?php

namespace Lunar\Core\Contracts;

interface HasThumbnailImage
{
    /**
     * Return the thumbnail image as a string.
     */
    public function getThumbnailImage(): string;
}
