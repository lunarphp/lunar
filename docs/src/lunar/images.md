# Images

[[toc]]

## Overview

For handling media across Lunar we use the brilliant [Laravel-medialibrary](https://spatie.be/docs/laravel-medialibrary) package by Spatie. We are committed to only bringing in additional dependencies when absolutely makes sense and we feel in this case, the medialibrary package offers a lot of features we would just end up trying to replicate anyway. Don't reinvent the wheel right?

For uploading images in the hub we are using [FilePond](https://pqina.nl).

### Base Configuration

Configuration is generally managed by the package itself, they do allow you to publish the configuration, but it's entirely optional. You can read more about the options available on the [medialibrary website](https://spatie.be/docs/laravel-medialibrary/v9/installation-setup)

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

## Conversions

Lunar provides some useful conversions which come ready out the box. This is provided in the config `lunar/media`.

```php
'conversions' => [
    \Lunar\Base\StandardMediaConversions::class,
],
```

You are free to use your own class to define your own conversions, just remember we will still apply our own `small` conversion as we need it in the hub.

Your own class could look something like:

```
namespace App\Media\Conversions;

class StorefrontConversions
{
    public function apply($model)
    {
        // .. Register media conversions here...
    }
}
```

For the full reference on what's possible, see [Defining Conversions](https://spatie.be/docs/laravel-medialibrary/v10/converting-images/defining-conversions).

Afterwards, simply add your conversion class to the `conversions` array, if you have no use for the standard ones we provide, simply remove the `StandardMediaConversions` reference.

```php
<?php

return [
    'conversions' => [
        \Lunar\Base\StandardMediaConversions::class,
        \App\Media\Conversions\StorefrontConversions::class
    ],
];
```

To regenerate conversions, e.g. if you have changed the configuration, you can run the following command.

```sh
php artisan media-library:regenerate
```

This will create queue jobs for each media entry to be re-processed. More information can be found on the [medialibrary website](https://spatie.be/docs/laravel-medialibrary/v9/converting-images/regenerating-images)


## Extending

You can extend your own models to use media, either by using our implementation or by implementing medialibrary directly. It's totally up to you and your requirements. If you want to use medialibrary directly, [just follow their guides](https://spatie.be/docs/laravel-medialibrary/v9/basic-usage/preparing-your-model) and you'll be all set.

::: warning
If you decide to use medialibrary directly, you will not have access to our transformations or any other Lunar features we add.
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
