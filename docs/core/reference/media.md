# Media

## Overview

For handling media across Lunar we use the brilliant [Laravel-medialibrary](https://spatie.be/docs/laravel-medialibrary) package by Spatie. We are committed to 
only bringing in additional dependencies when absolutely makes sense and we feel in this case, the medialibrary package 
offers a lot of features we would just end up trying to replicate anyway. Don't reinvent the wheel right?

For uploading images in the hub we are using [FilePond](https://pqina.nl).

### Base Configuration

Configuration is generally managed by the package itself, they do allow you to publish the configuration, but it's 
entirely optional. You can read more about the options available on the [medialibrary website](https://spatie.be/docs/laravel-medialibrary/v9/installation-setup)

Below is a list of models which currently support media:

- `Lunar\Models\Product`
- `Lunar\Models\Collection`

## Adding media to models

If you've used the medialibrary package before you will feel right at home.

```php
$product = \Lunar\Models\Product::find(123);

$product->addMedia($request->file('image'))->toMediaCollection('images');
```

For more information on what's available, see [Associating files](https://spatie.be/docs/laravel-medialibrary/v9/basic-usage/associating-files)

## Fetching images

```php
$product = \Lunar\Models\Product::find(123);

$product->getMedia('images');
```
For more information on what's available, see [Retrieving media](https://spatie.be/docs/laravel-medialibrary/v9/basic-usage/retrieving-media)

## Fallback images
If your model does not contain any images, calling getFirstMediaUrl or getFirstMediaPath will return null. You can 
provide a fallback path/url in the config `lunar/media` or `.env`.
```php
'fallback' => [
    'url' => env('FALLBACK_IMAGE_URL', null),
    'path' => env('FALLBACK_IMAGE_PATH', null)
]
```

## Media Collections & Conversions

Lunar provides a way to customise the media collections and conversions for each model that implements the `HasMedia` 
trait. 

You will find the default settings in the config `lunar/media`. Here you can switch out the class that handles the 
registration of the media definitions. 

### Custom Media Definitions

To create custom media definitions you will want to create your own implementation along the lines of the code below.

When registering media definitions you can define not only the name but many other options.
See [Spatie Media Library](https://spatie.be/docs/laravel-medialibrary/v10/working-with-media-collections/defining-media-collections) for more information.

```php
use Lunar\Base\MediaDefinitionsInterface;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CustomMediaDefinitions implements MediaDefinitionsInterface
{
    public function registerMediaConversions(HasMedia $model, Media $media = null): void
    {
        // Add a conversion for the admin panel to use
        $model->addMediaConversion('small')
            ->fit(Manipulations::FIT_FILL, 300, 300)
            ->sharpen(10)
            ->keepOriginalImageFormat();
    }

    public function registerMediaCollections(HasMedia $model): void
    {
        $fallbackUrl = config('lunar.media.fallback.url');
        $fallbackPath = config('lunar.media.fallback.path');

        // Reset to avoid duplication
        $model->mediaCollections = [];

        $collection = $model->addMediaCollection('images');

        if ($fallbackUrl) {
            $collection = $collection->useFallbackUrl($fallbackUrl);
        }

        if ($fallbackPath) {
            $collection = $collection->useFallbackPath($fallbackPath);
        }

        $this->registerCollectionConversions($collection, $model);
    }

    protected function registerCollectionConversions(MediaCollection $collection, HasMedia $model): void
    {
        $conversions = [
            'zoom' => [
                'width' => 500,
                'height' => 500,
            ],
            'large' => [
                'width' => 800,
                'height' => 800,
            ],
            'medium' => [
                'width' => 500,
                'height' => 500,
            ],
        ];

        $collection->registerMediaConversions(function (Media $media) use ($model, $conversions) {
            foreach ($conversions as $key => $conversion) {
                $model->addMediaConversion($key)
                    ->fit(
                        Manipulations::FIT_FILL,
                        $conversion['width'],
                        $conversion['height']
                    )->keepOriginalImageFormat();
            }
        });
    }
}
```
Then register your new class against the model(s) you wish to use it.

```php
return [

    'definitions' => [
        Lunar\Models\Product::class => CustomMediaDefinitions::class,
        //..
    ],

    //..
```

#### Generate Media Conversions

To regenerate conversions, e.g. if you have changed the configuration, you can run the following command.

```sh
php artisan media-library:regenerate
```

This will create queue jobs for each media entry to be re-processed. More information can be found on the 
[medialibrary website](https://spatie.be/docs/laravel-medialibrary/v9/converting-images/regenerating-images)


## Extending

You can extend your own models to use media, either by using our implementation or by implementing medialibrary 
directly. It's totally up to you and your requirements. If you want to use medialibrary directly, 
[just follow their guides](https://spatie.be/docs/laravel-medialibrary/v9/basic-usage/preparing-your-model) and you'll be all set.

::: warning
If you decide to use medialibrary directly, you will not have access to our transformations or any other Lunar features 
we add.
:::

### Extending with Lunar

To enable image transformations on your models within Lunar, simply add the `HasMedia` trait.

```php
<?php

namespace App\Models;

use Lunar\Base\Traits\HasMedia;

class YourCustomModel extends Model
{
    use HasMedia;
}
```

Now your models will auto generate transforms as defined in your configuration and still use medialibrary under the hood.
