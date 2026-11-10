<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Media\Fixture\RetypeGetThumbnailImageRector;

class ProductThumbnail
{
    public function getThumbnailImage(): string
    {
        return $this->thumbnail?->getUrl('small') ?? '';
    }
}

class VariantThumbnail
{
    public function getThumbnailImage(): string
    {
        return $this->getThumbnail()?->getUrl('small') ?? '';
    }
}

class EmptyThumbnail
{
    public function getThumbnailImage(): string
    {
        return '';
    }
}

class UrlThumbnail
{
    public function getThumbnailImage(): string
    {
        return $this->url;
    }
}

?>
-----
<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Media\Fixture\RetypeGetThumbnailImageRector;

class ProductThumbnail
{
    public function getThumbnailImage(): ?string
    {
        return $this->thumbnail?->getUrl('small') ?? null;
    }
}

class VariantThumbnail
{
    public function getThumbnailImage(): ?string
    {
        return $this->getThumbnail()?->getUrl('small') ?? null;
    }
}

class EmptyThumbnail
{
    public function getThumbnailImage(): ?string
    {
        return null;
    }
}

class UrlThumbnail
{
    public function getThumbnailImage(): ?string
    {
        return $this->url;
    }
}

?>
