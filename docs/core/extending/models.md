# Models

## Overview

Lunar provides a number of Eloquent Models and quite often in custom applications you will want to add your own relationships and functionality to these models.

::: warning
We highly suggest using your own Eloquent Models to add additional data, rather than trying to change fields on the core Lunar models.
:::

## Replaceable Models
All Lunar models are replaceable, this means you can instruct Lunar to use your own custom model, throughout the ecosystem, using dependency injection.


### Registration
We recommend registering your own models for your application within the boot method of your Service Provider.

When registering your models, you will need to set the Lunar model's contract as the first argument then your own model implementation for the second.


```php
/**
 * Bootstrap any application services.
 *
 * @return void
 */
public function boot()
{
    \Lunar\Facades\ModelManifest::replace(
        \Lunar\Models\Contracts\Product::class,
        \App\Model\Product::class,
    );
}
```

#### Registering multiple Lunar models.

If you have multiple models you want to replace, instead of manually replacing them one by one, you can specify a directory for Lunar to look in for Lunar models to use.
This assumes that each model extends its counterpart model i.e. `App\Models\Product` extends `Lunar\Models\Product`.

```php
/**
 * Bootstrap any application services.
 *
 * @return void
 */
public function boot()
{
    \Lunar\Facades\ModelManifest::addDirectory(
        __DIR__.'/../Models'
    );
}
```

### Route binding

Route binding is supported for your own routes and simply requires the relevant contract class to be injected.

```php
Route::get('products/{id}', function (\Lunar\Models\Contracts\Product $product) {
    $product; // App\Models\Product
});
```

### Relationship support

If you replace a model which is used in a relationship, you can easily get your own model back via relationship methods. Assuming we want to use our own instance of `App\Models\ProductVariant`.

```php
// In our service provider.
public function boot()
{
    \Lunar\Facades\ModelManifest::replace(
        \Lunar\Models\Contracts\ProductVariant::class,
        \App\Model\ProductVariant::class,
    );
}

// Somewhere else in your code...

$product = \Lunar\Models\Product::first();
$product->variants->first(); // App\Models\ProductVariant
```

### Static call forwarding

If you have custom methods in your own model, you can call those functions directly from the Lunar model instance.

Assuming we want to provide a new function to a product variant model.

```php
<?php

namespace App\Models;

class ProductVariant extends \Lunar\Models\ProductVariant
{
    public function someCustomMethod()
    {
        return 'Hello!';
    }
}
```

```php
// In your service provider.
public function boot()
{
    \Lunar\Facades\ModelManifest::replace(
        \Lunar\Models\Contracts\ProductVariant::class,
        \App\Model\ProductVariant::class,
    );
}
```

Somewhere else in your app...

```php
\Lunar\Models\ProductVariant::someCustomMethod(); // Hello!
\App\Models\ProductVariant::someCustomMethod(); // Hello!
```

### Observers

If you have observers in your app which call `observe` on the Lunar model, these will still work as intended when you replace any of the models, this means if you 
want to add your own custom observers, you can just reference the Lunar model and everything will be forwarded to the appropriate class.

```php
\Lunar\Models\Product::observe(/** .. */);
```

## Dynamic Eloquent Relationships

If you don't need to completely override or extend the Lunar models using the techniques above, you are still free to resolve relationships dynamically as Laravel provides out the box.

e.g. 

```php
use Lunar\Models\Order;
use App\Models\Ticket;
 
Order::resolveRelationUsing('ticket', function ($orderModel) {
    return $orderModel->belongsTo(Ticket::class, 'ticket_id');
});
```

See [https://laravel.com/docs/eloquent-relationships#dynamic-relationships](https://laravel.com/docs/eloquent-relationships#dynamic-relationships) for more information.
