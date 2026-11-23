<?php

namespace Lunar\Panel\Http\Controllers\Collections;

use Illuminate\Http\JsonResponse;
use Lunar\Core\Models\Collection;
use Lunar\Panel\Http\Controllers\Concerns\StagesMedia;
use Lunar\Panel\Http\Requests\Collections\CollectionMediaStoreRequest;

class CollectionMediaController
{
    use StagesMedia;

    public function store(CollectionMediaStoreRequest $request, Collection $collection): JsonResponse
    {
        return $this->stage($request, $collection);
    }
}
