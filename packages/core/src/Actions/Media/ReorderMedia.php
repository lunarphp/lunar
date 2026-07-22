<?php

namespace Lunar\Core\Actions\Media;

use InvalidArgumentException;
use Lunar\Core\Contracts\Actions\Media\ReordersMedia;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Persist a new media order for a model's collection and promote the first
 * item to primary (MediaObserver demotes the rest).
 */
class ReorderMedia implements ReordersMedia
{
    public function execute(HasMedia $model, array $ids, ?string $collection = null): void
    {
        $collection ??= config('lunar.media.collection');

        $media = $model->getMedia($collection);

        $currentIds = $media->pluck('id')->map(fn ($id) => (int) $id)->sort()->values();
        $givenIds = collect($ids)->map(fn ($id) => (int) $id)->sort()->values();

        if (! $currentIds->toArray() || $currentIds->toArray() !== $givenIds->toArray()) {
            throw new InvalidArgumentException(
                'Reorder ids must be exactly the media ids of the collection being ordered.'
            );
        }

        Media::setNewOrder($ids);

        // Only the image collection carries a primary/thumbnail; other
        // collections (e.g. document downloads) reorder without one.
        if ($collection !== config('lunar.media.collection')) {
            return;
        }

        $first = $media->firstWhere('id', (int) $ids[0]);

        if ($first && $first->getCustomProperty('primary') !== true) {
            $first->setCustomProperty('primary', true)->save();
        }
    }
}
