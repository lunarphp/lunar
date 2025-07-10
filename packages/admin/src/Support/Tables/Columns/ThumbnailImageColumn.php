<?php

namespace Lunar\Admin\Support\Tables\Columns;

use Filament\Tables\Columns\ImageColumn;

class ThumbnailImageColumn extends ImageColumn
{
    protected \Closure $resolveThumbnailUrlUsing;

    public function getImageUrl(?string $state = null): ?string
    {
        if ($this->resolveThumbnailUrlUsing) {
            return $this->evaluate($this->resolveThumbnailUrlUsing);
        }

        return $this->getRecord()?->getThumbnailImage();
    }

    public function resolveThumbnailUrlUsing(\Closure $callback): self
    {
        $this->resolveThumbnailUrlUsing = $callback;

        return $this;
    }
}
