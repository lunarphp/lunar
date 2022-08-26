# Models

[[toc]]

## Overview

GetCandy provides a number of Eloquent Models and quite often in custom applications you will want to add your own relationships and functionality to these models.

::: warning
We highly suggest using your own Eloquent Models to add additional data, rather than trying to change fields on the core GetCandy models.
:::

### Introducing Extendable Models

::: tip NEW TO GETCANDY
All GetCandy models are now extendable.
What does this mean for you? It means you can now add your own functionality or change our existing core model behaviour with your own model implementations.
See further details below.
:::

## Extendable

We recommend registering your own models for your application within the boot method of your Service Provider.

Note: You can register model manifests to any service provider which will either extend or override your models. This of course depends on the order of your service providers.
Please see further examples below.

#### Model Registration:
When registering your models, you need to set the key to the core model and then the value to your own model implementation. 
Here is an example below where we are extending 10 core models

```php
use GetCandy\Models\Product;
use GetCandy\Models\ProductVariant;
use GetCandy\Models\ProductOption;
use GetCandy\Models\ProductOptionValue;
use GetCandy\Models\Collection;
use GetCandy\Models\Customer;
use GetCandy\Models\Cart;
use GetCandy\Models\CartLine;
use GetCandy\Models\Order;
use GetCandy\Models\OrderLine;

/**
 * Bootstrap any application services.
 *
 * @return void
 */
public function boot()
{
    $models = collect([
        Product::class => \App\Models\Product::class,
        ProductVariant::class => \App\Models\ProductVariant::class,
        ProductOption::class => \App\Models\ProductOption::class,
        ProductOptionValue::class => \App\Models\ProductOptionValue::class,
        Collection::class => \App\Models\Collection::class,
        Customer::class => \App\Models\Customer::class,
        Cart::class => \App\Models\Cart::class,
        CartLine::class => \App\Models\CartLine::class,
        Order::class => \App\Models\Order::class,
        OrderLine::class => \App\Models\OrderLine::class,
    ]);

    ModelManifest::register($models);
}
```

#### Product Option Model Example 1 - Adding static method:
You are required to extend the core model `GetCandy\Models\ProductOption` in order for the relationships to function correctly.

```php
namespace App\Models;

use Illuminate\Support\Collection;

class ProductOption extends \GetCandy\Models\ProductOption
{
    public static function getSizes(): Collection
    {
        return static::whereHandle('size')->first()->values;
    }
}
```

In the example above you may now access your new methods via `\GetCandy\Models\ProductOption::getSizes()`
Note: Static methods will not allow you to jump to the function declaration.

You could of course access via `ProductOption::getSizes()` which will give you full IDE support, however please be aware the core model will not be aware of this new static method.

If jumping to a static function declaration is really important to you, then simply add inline docblock for example: 

```php
`/** @see \App\Models\ProductOption::getSizesStatic() */`
$newStaticMethod = \GetCandy\Models\ProductOption::getSizesStatic();
```

#### Product Model Example 2 - Overriding trait method:
You are required to extend the core model `GetCandy\Models\Product` in order for the relationships to function correctly.

```php
namespace App\Models;

use App\Concerns\SearchableTrait

class Product extends \GetCandy\Models\Product
{
    use SearchableTrait;
}
```
Note: shouldBeSearchable could also be directly overridden adding directly to the class.
This is an exmaple to shwo you how the core model can be made aware of your own models and traits.
What this also means is because you can now access your own models and traits via the core model. Scout will also be made aware that shouldBeSearchable will be false.

```php
namespace App\Concerns;

trait SearchableTrait
{
    /**
     * Determine if the model should be searchable.
     * @see \Laravel\Scout\Searchable::shouldBeSearchable()
     *
     * @return bool
     */
    public function shouldBeSearchable()
    {
        return false;
    }
}
```
#### Cart Model Example 3 - Overriding cart address functionality:
You are required to extend the core model `GetCandy\Models\Cart` in order for the relationships to function correctly.

```php
namespace App\Models;

use App\Concerns\HasAddresses;
use Illuminate\Database\Eloquent\Casts\AsCollection;

/**
 * Class Cart
 *
 * @property \Illuminate\Support\Collection $billingAddress
 * @property \Illuminate\Support\Collection $shippingAddress
 *
 * @see \GetCandy\Models\Cart
 */
class Cart extends \GetCandy\Models\Cart
{
    use HasAddresses;

    /**
     * {@inheritDoc}
     */
    protected $casts = [
        'completed_at' => 'datetime',
        'meta' => 'object',
        'legacy_data' => AsCollection::class,
    ];
}
```
Note: You can override the casts in the model for example migrating over from another platform. 
In this example we are setting legacy_data cast to store as json. (You will of course need to create your migration first)

The trait example is perhaps a little extreme but demonstrates how to fully extend the core model.

```php
namespace App\Concerns;

trait HasAddresses
{
    /**
     * Return the address relationships.
     *
     * @return \Illuminate\Support\Collection
     */
    public function addresses(): Collection
    {
        return ! $this->isSameAddress()
            ? $this->billingAddress->merge($this->shippingAddress)
            : $this->billingAddress;
    }

    /**
     * Return the shipping address relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function shippingAddress()
    {
        return $this->belongsTo(Address::class, 'shipping_address_id');
    }

    /**
     * Return the billing address relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function billingAddress()
    {
        return $this->belongsTo(Address::class, 'billing_address_id');
    }

    /**
     * Compare the shipping and billing address to see if they are the same.
     *
     * @return bool
     */
    protected function isSameAddress(): bool
    {
        return $this->billingAddress->first()->id === $this->shippingAddress->first()->id;
    }
}
```

## Swappable

This one is a little different from extendable. You can swap out the core model with your own model without registering.
You will currently not get the same benefits where the core model will be made aware or be able to override methods.
However when you swap the instance a new instance will be made including the attributes on the core model.

```php
namespace App\Models;

use GetCandy\Models\ProductVariant;

class ProductSwapModel extends \GetCandy\Models\Product
{
    /**
     * This will return the default variant for the product.
     *
     * @return bool
     */
    public function defaultVariant(): ProductVariant
    {
        return $this->variants->first();
    }
}
```

```php
use GetCandy\Models\Order;
use App\Models\Ticket;
 
/** @var \GetCandy\Models\Product $product */
$coreProductModel = Product::find(1);

$newProductModel = $product->swap(
    \App\Models\ProductSwapModel::class
);

// Note: As we have now swapped the model implementation, your model is aware of the attributes, we can now fetch the default variant for product id 1.
$default = $newProductModel->defaultVariant();
```

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
