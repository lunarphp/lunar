<?php

namespace Lunar\Filament\Tables\Columns;

use Closure;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Database\Eloquent\Model;

class ThumbnailImageColumn extends ImageColumn
{
    protected ?Closure $resolveThumbnailUrlUsing = null;

    public function getImageUrl(?string $state = null, ?Model $relatedRecord = null): ?string
    {
        if ($this->resolveThumbnailUrlUsing) {
            return $this->evaluate($this->resolveThumbnailUrlUsing);
        }

        return $this->getRecord()?->getThumbnailImage();
    }

    public function resolveThumbnailUrlUsing(Closure $callback): self
    {
        $this->resolveThumbnailUrlUsing = $callback;

        return $this;
    }
}
