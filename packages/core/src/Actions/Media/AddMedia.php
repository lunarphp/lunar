<?php

namespace Lunar\Core\Actions\Media;

use Illuminate\Http\UploadedFile;
use Lunar\Core\Contracts\Actions\Media\AddsMedia;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Attach an uploaded file to a model's media collection. When the collection
 * was empty, MediaObserver promotes the new media to primary.
 */
class AddMedia implements AddsMedia
{
    public function execute(HasMedia $model, UploadedFile $file, ?string $collection = null, array $customProperties = []): Media
    {
        return $model
            ->addMedia($file)
            ->withCustomProperties($customProperties)
            ->toMediaCollection($collection ?? config('lunar.media.collection'));
    }
}
