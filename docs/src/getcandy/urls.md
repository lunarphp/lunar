# URLs

[[toc]]

## Overview

URLs are not to be confused with Routes in Laravel. You can create routes for a number of resources, but most commonly they would be created for a Product.

They allow you to add a way to identify/query for a resource without having to use the ID of that resource. Notably these would be useful for vanity URLs so instead of something like:

```bash
/products/1
```

On your storefront, you could have:

```bash
/products/apple-iphone
```

`apple-iphone` is the slug for a URL which would correspond to a product and would allow you to fetch it easily without having to expose IDs or do any weird round trips to your API.

::: tip
A URL cannot share the same `slug` and `language_id` columns. You can also only have one `default` URL per language for that resource.
:::

## Creating a URL

```php
\GetCandy\Models\Url::create([
    'slug' => 'apple-iphone',
    'language_id' => $language->id,
    'default' => true,
]);
```

::: tip
If you add a new default URL for a language which already has one, the new URL will override and become the new default.
:::

```php
$urlA = \GetCandy\Models\Url::create([
    'slug' => 'apple-iphone',
    'language_id' => 1,
    'default' => true,
]);

$urlA->default // true

$urlB = \GetCandy\Models\Url::create([
    'slug' => 'apple-iphone-26',
    'language_id' => 1,
    'default' => true,
]);

$urlA->default // false
$urlB->default // true

/**
 * Since this is a different language, no other URLs will be changed.
 **/
$urlC = \GetCandy\Models\Url::create([
    'slug' => 'apple-iphone-french',
    'language_id' => 2,
    'default' => true,
]);


$urlA->default // false
$urlB->default // true
$urlC->default // true
```

## Deleting a URL

When you delete a URL, if it was the default then GetCandy will look for a non default of the same language and assign that instead.


```php
$urlA = \GetCandy\Models\Url::create([
    'slug' => 'apple-iphone',
    'language_id' => 1,
    'default' => true,
]);

$urlB = \GetCandy\Models\Url::create([
    'slug' => 'apple-iphone-26',
    'language_id' => 1,
    'default' => false,
]);

$urlB->default // false

$urlA->delete();

$urlB->default // true
```


## Adding URL support to Models

Out the box GetCandy has a few pre-configured models which have URLs

- Products
- Collections

You are free to add URLs to your own models.

```php
<?php

namespace App\Models;

use GetCandy\Base\Traits\HasUrls;

// ...

class MyModel extends Model
{
    use HasUrls;
}
```


You will then have access to the `url` relationship which is Polymorphic.

```php
$myModel->urls; // Collection
```

## Automatically generating URLs

You can tell GetCandy to generate URLs for models that use the `HasUrls` trait automatically by setting the `generator` config option in `config/getcandy/urls.php`.

By default this is set to `GetCandy\Generators\UrlGenerator::class` which means URLs will be generated. To disable this, set the config like below:

```php
return [
    'generator' => null
];
```

By default this will use the default language and take the `name` attribute as the slug, you are of course free to use your own class for this. You just need to make sure there is a `handle` method which accepts a `Model`.

```php
<?php

namspace App\Generators;

use Illuminate\Database\Eloquent\Model;

class MyCustomUrlGenerator
{
    public function handle(Model $model)
    {
        // ...
    }
}
```