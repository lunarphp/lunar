<?php

namespace Lunar\Observers;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaObserver
{
    public function created(Media $media): void
    {
        $this->ensureOnlyOnePrimary($media);
    }

    public function updated(Media $media): void
    {
        $this->ensureOnlyOnePrimary($media);
    }

    public function deleted(Media $media)
    {
        $this->ensureOnlyOnePrimary($media, isDelete: true);
    }

    protected function ensureOnlyOnePrimary(Media $media, bool $isDelete = false): void
    {
        if (config('lunar.media.collection') !== $media->collection_name) {
            return;
        }

        $owner = $media->model()->sole();

        if (! $isDelete && $media->getCustomProperty('primary')) {
            $owner->getMedia($media->collection_name)
                ->reject(fn ($collectionMedia) => $collectionMedia->id == $media->id || $collectionMedia->getCustomProperty('primary') === false)
                ->each(fn ($collectionMedia) => $collectionMedia->setCustomProperty('primary', false)->saveQuietly());
        } else {
            $collection = $owner->getMedia($media->collection_name)
                ->reject(fn ($collectionMedia) => $collectionMedia->id == $media->id);
            $primaryCollection = $collection
                ->filter(fn ($collectionMedia) => $collectionMedia->getCustomProperty('primary') === true);

            $collectionCount = $collection->count();
            $primaryCount = $primaryCollection->count();

            if ($isDelete && $collectionCount == 0) {
                return;
            }

            if ($collectionCount == 0) {
                $media->refresh()->setCustomProperty('primary', true)
                    ->saveQuietly();
            } elseif ($primaryCount == 0) {
                $collection->first()->setCustomProperty('primary', true)
                    ->saveQuietly();
            } elseif ($primaryCount > 1) {
                $first = $primaryCollection->first();

                $first->setCustomProperty('primary', true)
                    ->saveQuietly();

                $primaryCollection->reject(fn ($collectionMedia) => $collectionMedia->id == $first->id)
                    ->each(fn ($collectionMedia) => $collectionMedia->setCustomProperty('primary', false)->saveQuietly());
            }
        }
    }
}
