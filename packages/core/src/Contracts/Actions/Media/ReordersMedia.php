<?php

namespace Lunar\Core\Contracts\Actions\Media;

use Spatie\MediaLibrary\HasMedia;

interface ReordersMedia
{
    /**
     * @param  array<int, int>  $ids  every media id in the collection, in the new order; the first becomes primary
     * @param  ?string  $collection  defaults to the configured lunar.media.collection
     */
    public function execute(HasMedia $model, array $ids, ?string $collection = null): void;
}
