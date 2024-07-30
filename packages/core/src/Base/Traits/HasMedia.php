<?php

namespace Lunar\Base\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Lunar\Base\StandardMediaDefinitions;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait HasMedia
{
    use InteractsWithMedia;

    protected array $mediaCollectionTitles = [];

    protected array $mediaCollectionDescriptions = [];

    /**
     * Boot the trait
     */
    protected static function bootHasMedia(array $attributes = []): void
    {
        static::retrieved(function (Model $model) {
            // Set media collection titles and descriptions
            $mediaDefinition = app($model->getDefinitionClass());
            $model->mediaCollectionTitles = $mediaDefinition->getMediaCollectionTitles();
            $model->mediaCollectionDescriptions = $mediaDefinition->getMediaCollectionDescriptions();
        });
    }

    /**
     * Relationship for thumbnail.
     */
    public function thumbnail(): MorphOne
    {
        return $this->morphOne(config('media-library.media_model'), 'model')
            ->where('custom_properties->primary', true);
    }

    public function registerMediaCollections(): void
    {
        $mediaDefinition = app($this->getDefinitionClass());
        $mediaDefinition->registerMediaCollections($this);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        app($this->getDefinitionClass())->registerMediaConversions($this, $media);
    }

    public function getMediaCollectionTitle(string $name): string
    {
        return $this->mediaCollectionTitles[$name] ?? 'Media';
    }

    public function getMediaCollectionDescription(string $name): string
    {
        return $this->mediaCollectionDescriptions[$name] ?? '';
    }

    protected function getDefinitionClass()
    {
        $conversionClasses = config('lunar.media.definitions', []);

        return $conversionClasses[static::class] ?? StandardMediaDefinitions::class;
    }
}
