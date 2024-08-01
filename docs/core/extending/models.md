# Models

## Overview

Lunar provides a number of Eloquent Models and quite often in custom applications you will want to add your own relationships and functionality to these models.

::: warning
We highly suggest using your own Eloquent Models to add additional data, rather than trying to change fields on the core Lunar models.
:::

## Extendable Models
All Lunar models are now extendable.
This means you can now add your own functionality or change out existing core model behaviour using your own model implementations.

### Registration:
We recommend registering your own models for your application within the boot method of your Service Provider.
When registering your models, you will need to set the Lunar core model as the key and then your own model implementation as the value.

Here is an example below where we are extending 10 core models from your main AppServiceProvider:

```php
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;
use Lunar\Models\ProductOption;
use Lunar\Models\ProductOptionValue;
use Lunar\Models\Collection;
use Lunar\Models\Customer;
use Lunar\Models\Cart;
use Lunar\Models\CartLine;
use Lunar\Models\Order;
use Lunar\Models\OrderLine;

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

### Swap Implementation
You can override the model implementation at any time by calling the swap method on the core model.
When you call the swap method this will update the key value pair for the registered model. If you need to go back to the previous implementation then simply call the swap method again passing through your registered implementation.

```php
namespace App\Models;

use Lunar\Models\ProductVariant;

class ProductSwapModel extends \Lunar\Models\Product
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
$product = \Lunar\Models\Product::find(1);

// This will swap out the registered implementation.
$product->swap(\App\Models\ProductSwapModel::class);

// You can now call this new method
$default = $product->defaultVariant();

// Swap again to go back to your original implementation or perhaps define a new one.
$product->swap(\App\Models\Product::class);
```

### Examples
Here are some example simple use cases of extending the core models.
You are required to extend the core model `Lunar\Models\[Model]` in order for the relationships to function correctly.

#### Example 1 - Adding static method (ProductOption Model)

```php
namespace App\Models;

use Illuminate\Support\Collection;

class ProductOption extends \Lunar\Models\ProductOption
{
    public static function getSizes(): Collection
    {
        return static::whereHandle('size')->first()->values;
    }
}
```
In this example you can access the static method via `\Lunar\Models\ProductOption::getSizes()`
Note: Static methods will not allow you to jump to the function declaration.
As a workaround simply add @see inline docblock:

```php
`/** @see \App\Models\ProductOption::getSizesStatic() */`
$newStaticMethod = \Lunar\Models\ProductOption::getSizesStatic();
```

#### Example 2 - Overriding trait method (Product Model)

```php
namespace App\Models;

use App\Concerns\SearchableTrait;

class Product extends \Lunar\Models\Product
{
    use SearchableTrait;
}
```
Note: shouldBeSearchable could also be overridden by adding directly to the Product class above.
In this example we are showing you how the core model can be made aware of your own model and trait methods.

What this also means now the core model can forward call to your extended methods. 
Scout in this case will also be made aware that shouldBeSearchable will return false.

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
#### Example 3 - Overriding cart address functionality (Cart Model)

```php
namespace App\Models;

use App\Concerns\HasAddresses;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\AsCollection;

/**
 * Class Cart
 *
 * @property \Illuminate\Support\Collection $billingAddress
 * @property \Illuminate\Support\Collection $shippingAddress
 *
 */
class Cart extends \Lunar\Models\Cart
{
    use HasAddresses;

    /**
     * {@inheritDoc}
     */
    protected $casts = [
        'completed_at' => 'datetime',
        'meta' => AsArrayObject::class,
        'shipping_data' => AsCollection::class,
    ];
}
```
Note: You can override the casts in a model for example useful when adding new json fields. 
In this example we are setting shipping_data cast to store as json. (You will of course need to create your migration)

The trait below demonstrates how to fully extend the cart model functionality.

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

## Dynamic Eloquent Relationships

Eloquent relationships can be dynamically specified in code, allowing you to add additional relationships to the Lunar Models.

e.g. 

```php
use Lunar\Models\Order;
use App\Models\Ticket;
 
Order::resolveRelationUsing('ticket', function ($orderModel) {
    return $orderModel->belongsTo(Ticket::class, 'ticket_id');
});
```

See [https://laravel.com/docs/eloquent-relationships#dynamic-relationships](https://laravel.com/docs/eloquent-relationships#dynamic-relationships) for more information.


## Macroable

All Lunar models have been made macroable. This is a Laravel technique to allow a developer to dynamically add methods to an existing class. This is ideal for adding helpful functions for your application.

Here is an example...

```php
use Lunar\Models\Product;

Product::macro('isDraft', function () {
    return $this->status === 'draft';
});
```
