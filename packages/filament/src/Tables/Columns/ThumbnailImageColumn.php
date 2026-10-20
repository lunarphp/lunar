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
            if ($this->resolveThumbnailUrlUsing) {
                return $this->evaluate($this->resolveThumbnailUrlUsing);
            }

            return $this->getRecord()?->getThumbnailImage();
        });
    }

    public function resolveThumbnailUrlUsing(Closure $callback): self
    {
        $this->resolveThumbnailUrlUsing = $callback;

        return $this;
    }
}
