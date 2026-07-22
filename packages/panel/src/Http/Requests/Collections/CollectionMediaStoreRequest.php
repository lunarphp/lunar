<?php

namespace Lunar\Panel\Http\Requests\Collections;

use Lunar\Core\Models\Collection;
use Lunar\Panel\Http\Requests\Media\MediaStoreRequest;
use Spatie\MediaLibrary\HasMedia;

class CollectionMediaStoreRequest extends MediaStoreRequest
{
    protected function mediaModel(): HasMedia
    {
        /** @var Collection $collection */
        $collection = $this->route('collection');

        return $collection;
    }
}
