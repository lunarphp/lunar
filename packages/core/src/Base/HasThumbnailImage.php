<?php

namespace Lunar\Base;

interface HasThumbnailImage
{
    /**
     * Return the thumbnail image as a string.
     */
    public function getThumbnailImage(): string;
}
