<?php

namespace Lunar\Core\Contracts\Actions\Media;

use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

interface AddsMedia
{
    /**
     * @param  ?string  $collection  defaults to the configured lunar.media.collection
     * @param  array<string, mixed>  $customProperties
     */
    public function execute(HasMedia $model, UploadedFile $file, ?string $collection = null, array $customProperties = []): Media;
}
