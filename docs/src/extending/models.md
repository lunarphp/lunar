# Models

[[toc]]

## Overview

GetCandy provides a number of Eloquent Models and quite often in custom applications you will want to add your own relationships and functionality to these models.

::: warning
We highly suggest using your own Eloquent Models to add additional data, rather than trying to change fields on the core GetCandy models.
:::


## Dynamic Eloquent Relationships

Eloquent relationships can be dynamically specified in code, allowing you to add additional relationships to the GetCandy Models.

e.g. 

```php
use GetCandy\Models\Order;
use App\Models\Ticket;
 
Order::resolveRelationUsing('ticket', function ($orderModel) {
    return $orderModel->belongsTo(Ticket::class, 'ticket_id');
});
```

See [https://laravel.com/docs/9.x/eloquent-relationships#dynamic-relationships]([https://laravel.com/docs/9.x/eloquent-relationships#dynamic-relationships) for more information.


## Macroable

All GetCandy models have been made macroable. This is a Laravel technique to allow a developer to dynamically add methods to an existing class. This is ideal for adding helpful functions for your application.

Here is an example...

```php
use GetCandy\Models\Product;

Product::macro('isDraft', function () {
    return $this->status === 'draft';
});
```

## Extendable

All GetCandy models are now extendable.
Simply register the model mapping for your application within the register method of your Service Provider

Here is an example below on registering your models.

```php
use GetCandy\Models\Product;
use GetCandy\Models\ProductOption;

ModelFactory::register([
    Product::class => \App\Models\Product::class,
    ProductOption::class => \App\Models\ProductOption::class,
]);
```

Example for extended model usage below.

Note: You are not required to extend `GetCandy\Models\ProductOption` you could just extend with `Illuminate\Database\Eloquent\Model`
This would allow you to completely override the model for more advanced usage. However, the recommended approach is to extend allowing you to access GetCandy core model methods.

```php
use GetCandy\Models\ProductOption as ProductOptionBase;
use Illuminate\Support\Collection;

class ProductOption extends ProductOptionBase
{
    public static function getSizes(): Collection
    {
        return static::whereHandle('size')->first()->values;
    }
}
```

In the example above you may now access your new methods via `ProductOptionBase::getSizes()` - static methods currently do not support IDE auto-completion.  

Note: The way this works, all calls to your extended class methods are forwarded from the GetCandy model class.
