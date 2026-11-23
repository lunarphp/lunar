<?php

namespace Lunar\Panel\Support\Media;

use Illuminate\Support\Str;
use Lunar\Core\Contracts\MediaDefinitions;
use Lunar\Core\Media\StandardDefinitions;
use Lunar\Panel\Models\EditDraft;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Shapes a model's registered media collections into the per-group props the
 * panel edit pages render, one section per collection. Image collections
 * carry the rich-uploader shape; every other collection is a downloadable
 * file group. Which items a group shows, in what order and with what
 * properties, is the `media:{collection}` draft value; `library` only holds
 * each item's preview, for the record's media and for uploads staged on the
 * staff member's draft.
 */
class MediaGroups
{
    /**
     * @param  string  $routeNamePrefix  e.g. "panel.products"; the media.store route name hangs off it.
     * @return array<int, array<string, mixed>>
     */
    public static function for(HasMedia $model, string $routeNamePrefix, ?EditDraft $draft = null): array
    {
        $definition = static::definition($model);
        $titles = $definition->getMediaCollectionTitles();
        $descriptions = $definition->getMediaCollectionDescriptions();
        $staged = $draft ? $draft->media()->get() : collect();

        return $model->getRegisteredMediaCollections()
            ->map(function (MediaCollection $collection) use ($model, $routeNamePrefix, $titles, $descriptions, $staged): array {
                $isImage = static::isImageCollection($model, $collection->name);

                return [
                    'collection' => $collection->name,
                    'title' => $titles[$collection->name] ?? Str::headline($collection->name),
                    'description' => $descriptions[$collection->name] ?? '',
                    'type' => $isImage ? 'image' : 'file',
                    'accept' => $isImage ? 'image/*' : implode(',', $collection->acceptsMimeTypes),
                    'library' => $model->getMedia($collection->name)
                        ->concat($staged->where('collection_name', $collection->name))
                        ->map(fn (Media $item) => static::item($item, $isImage))
                        ->values()
                        ->all(),
                    'urls' => [
                        'store' => route("{$routeNamePrefix}.media.store", $model),
                    ],
                ];
            })
            ->values()
            ->all();
    }

    public static function isImageCollection(HasMedia $model, string $name): bool
    {
        if ($name === config('lunar.media.collection')) {
            return true;
        }

        $collection = $model->getRegisteredMediaCollections()->firstWhere('name', $name);

        return $collection instanceof MediaCollection
            && $collection->acceptsMimeTypes !== []
            && collect($collection->acceptsMimeTypes)->every(fn (string $mime) => Str::startsWith($mime, 'image/'));
    }

    /**
     * An item's preview: what a tile or file row shows besides its drafted
     * properties.
     *
     * @return array<string, mixed>
     */
    public static function item(Media $item, bool $isImage): array
    {
        if ($isImage) {
            return [
                'id' => $item->id,
                'url' => $item->getAvailableUrl(['small']),
                'original_url' => $item->getUrl(),
            ];
        }

        return [
            'id' => $item->id,
            'file_name' => $item->file_name,
            'mime_type' => $item->mime_type,
            'size' => $item->size,
            'extension' => pathinfo((string) $item->file_name, PATHINFO_EXTENSION),
            'original_url' => $item->getUrl(),
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
