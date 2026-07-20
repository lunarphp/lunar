<?php

namespace Lunar\Core\Actions\Media;

use Lunar\Core\Contracts\Actions\Media\UpdatesMedia;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\Conversions\ConversionCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Update a media item's custom properties: name, alt, caption, the focal
 * point ({x, y} integer percentages, 0-100), and the primary flag (only ever
 * promoted here — MediaObserver demotes siblings and re-points on delete).
 *
 * The focal custom property is the canonical value; display surfaces apply
 * it as CSS object-position. It is additionally mirrored into per-media
 * focalCrop manipulations for any conversion registered with Fit::Crop, so
 * cropped conversion files honour it too — saving the media regenerates
 * them. The standard definitions register no cropping conversions, so the
 * mirroring is a no-op unless a host app's media definitions crop.
 */
class UpdateMedia implements UpdatesMedia
{
    public function execute(Media $media, array $properties): Media
    {
        foreach (['name', 'alt', 'caption'] as $key) {
            if (array_key_exists($key, $properties)) {
                $media->setCustomProperty($key, $properties[$key]);
            }
        }

        if (isset($properties['focal'])) {
            $focal = [
                'x' => max(0, min(100, (int) $properties['focal']['x'])),
                'y' => max(0, min(100, (int) $properties['focal']['y'])),
            ];

            $media->setCustomProperty('focal', $focal);

            $manipulations = $this->focalCropManipulations($media, $focal);

            if ($manipulations !== []) {
                $media->manipulations = array_merge($media->manipulations, $manipulations);
            }
        }

        if ($properties['primary'] ?? false) {
            $media->setCustomProperty('primary', true);
        }

        $media->save();

        return $media;
    }

    /**
     * Build focalCrop manipulations for every conversion that crops.
     *
     * @param  array{x: int, y: int}  $focal
     * @return array<string, array{focalCrop: array{int, int, int, int}}>
     */
    protected function focalCropManipulations(Media $media, array $focal): array
    {
        if ($media->model === null) {
            return [];
        }

        $manipulations = [];

        foreach (ConversionCollection::createForMedia($media) as $conversion) {
            $fit = $conversion->getManipulations()->getManipulationArgument('fit');

            if (! is_array($fit) || count($fit) < 3) {
                continue;
            }

            [$fitType, $width, $height] = $fit;

            if (! $fitType instanceof Fit) {
                $fitType = Fit::tryFrom((string) $fitType);
            }

            if ($fitType !== Fit::Crop || ! $width || ! $height) {
                continue;
            }

            $manipulations[$conversion->getName()] = [
                'focalCrop' => [(int) $width, (int) $height, $focal['x'], $focal['y']],
            ];
        }

        return $manipulations;
    }
}
