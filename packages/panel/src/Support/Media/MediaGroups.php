<?php

namespace Lunar\Panel\Support\Media;

use Illuminate\Support\Str;
use Lunar\Core\Contracts\MediaDefinitions;
use Lunar\Core\Media\StandardDefinitions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Shapes a model's registered media collections into the per-group props the
 * panel edit pages render — one section per collection. Image collections carry
 * the rich-uploader shape; every other collection is a downloadable file group.
 */
class MediaGroups
{
    /**
     * @param  string  $routeNamePrefix  e.g. "panel.products" — the media.* route names hang off it.
     * @return array<int, array<string, mixed>>
     */
    public static function for(HasMedia $model, string $routeNamePrefix): array
    {
        $definition = static::definition($model);
        $titles = $definition->getMediaCollectionTitles();
        $descriptions = $definition->getMediaCollectionDescriptions();

        return $model->getRegisteredMediaCollections()
            ->map(function (MediaCollection $collection) use ($model, $routeNamePrefix, $titles, $descriptions): array {
                $isImage = static::isImageCollection($collection);

                return [
                    'collection' => $collection->name,
                    'title' => $titles[$collection->name] ?? Str::headline($collection->name),
                    'description' => $descriptions[$collection->name] ?? '',
                    'type' => $isImage ? 'image' : 'file',
                    'accept' => $isImage ? 'image/*' : implode(',', $collection->acceptsMimeTypes),
                    'items' => $model->getMedia($collection->name)
                        ->map(fn (Media $item) => $isImage
                            ? static::imageItem($item, $model, $routeNamePrefix)
                            : static::fileItem($item, $model, $routeNamePrefix))
                        ->values()
                        ->all(),
                    'urls' => [
                        'store' => route("{$routeNamePrefix}.media.store", $model),
                        'reorder' => route("{$routeNamePrefix}.media.reorder", $model),
                    ],
                ];
            })
            ->values()
            ->all();
    }

    protected static function isImageCollection(MediaCollection $collection): bool
    {
        if ($collection->name === config('lunar.media.collection')) {
            return true;
        }

        return $collection->acceptsMimeTypes !== []
            && collect($collection->acceptsMimeTypes)->every(fn (string $mime) => Str::startsWith($mime, 'image/'));
    }

    /**
     * @return array<string, mixed>
     */
    protected static function imageItem(Media $item, HasMedia $model, string $routeNamePrefix): array
    {
        return [
            'id' => $item->id,
            'url' => $item->getAvailableUrl(['small']),
            'original_url' => $item->getUrl(),
            'name' => $item->getCustomProperty('name'),
            'alt' => $item->getCustomProperty('alt'),
            'caption' => $item->getCustomProperty('caption'),
            'focal' => $item->getCustomProperty('focal'),
            'primary' => (bool) $item->getCustomProperty('primary'),
            'update_url' => route("{$routeNamePrefix}.media.update", [$model, $item]),
            'destroy_url' => route("{$routeNamePrefix}.media.destroy", [$model, $item]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected static function fileItem(Media $item, HasMedia $model, string $routeNamePrefix): array
    {
        return [
            'id' => $item->id,
            'file_name' => $item->file_name,
            'mime_type' => $item->mime_type,
            'size' => $item->size,
            'extension' => pathinfo((string) $item->file_name, PATHINFO_EXTENSION),
            'original_url' => $item->getUrl(),
            'name' => $item->getCustomProperty('name'),
            'caption' => $item->getCustomProperty('caption'),
            'update_url' => route("{$routeNamePrefix}.media.update", [$model, $item]),
            'destroy_url' => route("{$routeNamePrefix}.media.destroy", [$model, $item]),
        ];
    }

    protected static function definition(HasMedia $model): MediaDefinitions
    {
        $definitions = config('lunar.media.definitions', []);
        $class = $definitions[Str::snake(class_basename($model))]
            ?? $definitions[$model::class]
            ?? $definitions[get_parent_class($model)]
            ?? StandardDefinitions::class;

        return app($class);
    }
}
