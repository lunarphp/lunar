<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Media\Fixture\RetypeGetThumbnailImageRector;

class Unrelated
{
    public function render(): string
    {
        return $this->path ?? '';
    }

    public function getThumbnailImage(): ?string
    {
        return $this->thumbnail?->getUrl('small') ?? null;
    }
}
