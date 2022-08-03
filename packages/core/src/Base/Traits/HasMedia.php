<?php

namespace GetCandy\Base\Traits;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait HasMedia
{
    use InteractsWithMedia;

    /**
     * Relationship for thumbnail.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function thumbnail(): MorphOne
    {
        return $this->morphOne(config('media-library.media_model'), 'model')
            ->where('custom_properties->primary', true);
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $transforms = config('getcandy.media.transformations');

        // if (str_contains('image', $media->mime_type)) {
        collect($transforms)->each(function ($transform, $handle) {
            $conversion = $this->addMediaConversion($handle)
                    ->fit(
                        $transform['fit'] ?? Manipulations::FIT_FILL,
                        $transform['width'],
                        $transform['height']
                    );

            if ($collections = ($transform['collections'] ?? null)) {
                $conversion->collections($collections);
            }

            if ($border = ($transform['border'] ?? null)) {
                $conversion->border(
                        $border['size'],
                        $border['color'],
                        $border['type']
                    );
            }

            $conversion->keepOriginalImageFormat();
        });
        // }
    }
}
