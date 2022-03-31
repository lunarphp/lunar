# Images

[[toc]]

## Overview

For handling media across GetCandy we use the brilliant [Laravel-medialibrary](https://spatie.be/docs/laravel-medialibrary) package by Spatie. We are committed to only bringing in additional dependencies when absolutely makes sense and we feel in this case, the medialibrary package offers a lot of features we would just end up trying to replicate anyway. Don't reinvent the wheel right?

For uploading images in the hub we are using [FilePond](https://pqina.nl).

### Base Configuration

Configuration is generally managed by the package itself, they do allow you to publish the configuration, but it's entirely optional. You can read more about the options available on the [medialibrary website](https://spatie.be/docs/laravel-medialibrary/v9/installation-setup)

Below is a list of models which currently support media:

- `GetCandy\Models\Product`
- `GetCandy\Models\Collection`

## Adding media to models

If you've used the medialibrary package before you will feel right at home.

```php
$product = \GetCandy\Models\Product::find(123);

$product->addMedia($request->file('image'))->toMediaCollection();
```

For more information on what's available, see [Associating files](https://spatie.be/docs/laravel-medialibrary/v9/basic-usage/associating-files)

## Fetching media

```php
$product = \GetCandy\Models\Product::find(123);

$product->getMedia();
```
For more information on what's available, see [Retrieving media](https://spatie.be/docs/laravel-medialibrary/v9/basic-usage/retrieving-media)

## Transformations

We will automatically generate optimised versions of the uploaded image in pre-defined transformations.

To regenerate transformations, e.g. if you have changed the configuration, you can run the following command.


```sh
php artisan media-library:regenerate

```

This will create queue jobs for each media entry to be re-processed. More information can be found on the [medialibrary website](https://spatie.be/docs/laravel-medialibrary/v9/converting-images/regenerating-images)


GetCandy has their own set of transformations which are used throughout the system, stored in `config/getcandy/media.php`.

### Available options
Each transformation definition accepts the following options

|Key|Type|Description|Example|Default|
|:-|:-|:-|:-|:-|
|`width`|`integer`|The width of the transformation.|`500`|`null`
|`height`|`integer`|The height of the transformation.|`400`|`null`
|`collections`|`array`|An array of collections to limit the transformations to, by default they run across all of them.|`['images']`|`null`
|`border`|`array`|If you want to add a border to the image, define that here. For more information on the types available, see the [medialibrary website](https://spatie.be/docs/image/v1/image-manipulations/image-canvas#border)|See below|`null`
|`fit`|`string`|Specify how you want the image transformation to fit within the given width/height see [medialibrary website](https://spatie.be/docs/image/v1/image-manipulations/resizing-images#fit) for more info|See below|`FIT_CONTAIN`

```php
'transformations' => [
    'zoom' => [
        // ...
        'border' => [
            'size' => 10,
            'color' => 'black', // Can also be any HEX value
            'type' => \Spatie\Image\Manipulations::BORDER_OVERLAY
        ],
        'fit' => \Spatie\Image\Manipulations::FIT_CONTAIN
    ],
    'large' => [
        // ...
    ],
    'medium' => [
        // ...
    ],
    'small' => [
        // ...
    ],
],
```

## Extending

You can extend your own models to use media, either by using our implementation or by implementing medialibrary directly. It's totally up to you and your requirements. If you want to use medialibrary directly, [just follow their guides](https://spatie.be/docs/laravel-medialibrary/v9/basic-usage/preparing-your-model) and you'll be all set.

::: warning
If you decide to use medialibrary directly, you will not have access to our transformations or any other GetCandy features we add.
:::

### Extending with GetCandy

To enable image transformations on your models within GetCandy, simply add the `HasMedia` trait.

```php
<?php

namespace App\Models;

use GetCandy\Base\Traits\HasMedia;

class YourCustomModel extends Model
{
    use HasMedia;
}
```

Now your models will auto generate transforms as defined in your configuration and still use medialibrary under the hood.
