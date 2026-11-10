<?php

namespace Lunar\Filament\Tables\Columns;

use Closure;
use Filament\Tables\Columns\ImageColumn;

class ThumbnailImageColumn extends ImageColumn
{
    protected ?Closure $resolveThumbnailUrlUsing = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->getStateUsing(function (): ?string {
            $thumbnail = $this->resolveThumbnailUrlUsing
                ? $this->evaluate($this->resolveThumbnailUrlUsing)
                : $this->getRecord()?->getThumbnailImage();

            // A root-relative URL (media on a disk whose url has no host) is a
            // browser path, which Filament would otherwise read as a disk path.
            if (is_string($thumbnail) && str_starts_with($thumbnail, '/') && ! str_starts_with($thumbnail, '//')) {
                return url($thumbnail);
            }

            return $thumbnail;
        });
    }

    public function resolveThumbnailUrlUsing(Closure $callback): self
    {
        $this->resolveThumbnailUrlUsing = $callback;

        return $this;
    }
}
