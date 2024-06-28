# Orders

## Overview

As you'd expect, orders on an online system show what users have purchased. Orders are linked to Carts and although you
would generally only have one Order per cart, the system will support multiple if your store requires it.

```php
Lunar\Models\Order
```

| Field                 | Description                                                                                                        |
|:----------------------|:-------------------------------------------------------------------------------------------------------------------|
| id                    |                                                                                                                    |
| user_id               | If this is not a guest order, this will have the users id                                                          |
| customer_id           | Can be `null`, stores customer                                                                                     |
| cart_id               | The related cart                                                                                                   |
| channel_id            | Which channel this was purchased through                                                                           |
| status                | A status that makes sense to you as the store owner                                                                |
| reference             | Your stores own reference                                                                                          
| customer_reference    | If you want customers to add their own reference, it goes here.                                                    
| sub_total             | The sub total minus any discounts, excl. tax                                                                       
| discount_breakdown    | A json field for the discount breakdown e.g. `[{"discount_id": 1, "lines": [{"id": 1, "qty": 1}]], "total": 200}]` 
| discount_total        | Any discount amount excl. tax                                                                                      
| shipping_breakdown| A json field for the shipping breakdown e.g. `[{"name": "Standard Delivery", "identifier": "STD", "price": 123}]`
| shipping_total        | The shipping total with tax                                                                                       
| tax_breakdown         | A json field for the tax breakdown e.g. `[{"description": "VAT", "identifier" : "vat", "value": 123, "percentage": 20, "currency_code": "GBP"}]`                        
| tax_total             | The total amount of tax applied                                                                                    
| total                 | The grand total with tax                                                                                           
| notes                 | Any additional order notes                                                                                         
| currency_code         | The code of the currency the order was placed in                                                                   
| compare_currency_code | The code of the default currency at the time                                                                       
| exchange_rate         | The exchange rate between `currency_code` and `compare_currency_code`                                              
| placed_at             | The datetime the order was considered placed.                                                                      
| meta                  | Any additional meta info you wish to store                                                                         
| created_at            |                                                                                                                    |
| updated_at            |                                                                                                                    |

## Create an order

You can either create an order directly, or the recommended way is via a `Cart` model.

```php
$order = \Lunar\Models\Order::create([/** .. */]);

// Recommended way
$order = Cart::first()->createOrder(
    allowMultipleOrders: false,
    orderIdToUpdate: null,
);
```

- `allowMultipleOrders` - Generally carts will only have one draft order associated, however if you want to allow carts to
  have multiple, you can pass `true` here.
- `orderIdToUpdate` - You can optionally pass the ID of an order to update instead of attempting to create a new order, this must be a draft order i.e. a null `placed_at` and related to the cart.

The underlying class for creating an order is `Lunar\Actions\Carts\CreateOrder`, you are free to override this in the
config file `config/cart.php`

```php
return [
    //  ...
    'actions' => [
        // ...
        'order_create' => CustomCreateOrder::class
    ]
]
```

At minimum your class should look like the following:

```php
final class CreateOrder extends Lunar\Actions\AbstractAction
{
    /**
     * Execute the action.
     */
    public function execute(
        Cart $cart,
        bool $allowMultipleOrders = false,
        int $orderIdToUpdate = null
    ): self {
        return $this;
    }
}
```

### Validating a cart before creation.

If you also want to check before you attempt this if the cart is ready to create an order, you can call the helper
method:

```php
$cart->canCreateOrder();
```

Under the hood this will use the `ValidateCartForOrderCreation` class which lunar provides and throw any validation
exceptions with helpful messages if the cart isn't ready to create an order.

You can specify you're own class to handle this in `config/cart.php`.

```php
return [
    // ...
    'validators' => [
        'order_create' => MyCustomValidator::class,
    ]
]
```

Which may look something like:

```php
<?php

//...

class MyCustomValidator extends \Lunar\Validation\BaseValidator
{
    public function validate(): bool
    {
        $cart = $this->parameters['cart'];
        
        if ($somethingWentWrong) {
            return $this->fail('There was an issue');
        }
        
        return $this->pass();
    }
}
```

## Order Reference Generating

By default Lunar will generate a new order reference for you when you create an order from a cart. The format for this
is:

```
{year}-{month}-{0..0}{orderId}
```

`{0..0}` indicates the order id will be padded with up to four `0`'s for example:

```
2022-01-0001
2022-01-0011
2022-01-0111
2022-01-1111
```

### Custom Generators

If your store has a specific requirement for how references are generated, you can easily swap out the Lunar one for
your own:

`config/lunar/orders.php`

```php
return [
    'reference_generator' => App\Generators\MyCustomGenerator::class,
];
```

Or, if you don't want references at all (not recommended) you can simply set it to `null`

Here's the underlying class for the custom generator:

```php
namespace App\Generators;

use Lunar\Models\Order;

class MyCustomGenerator implements OrderReferenceGeneratorInterface
{
    /**
     * {@inheritDoc}
     */
    public function generate(Order $order): string
    {
        // ...
        return 'my-custom-reference';
    }
}
```

## Modifying Orders

If you need to programmatically change the Order values or add in new behaviour, you will want to extend the Order
system.

You can find out more in the Extending Lunar section for [Order Modifiers](/core/extending/orders).

## Order Lines

```php
Lunar\Models\OrderLine
```

| Field            | Description                                                                                                                                      |
|:-----------------|:-------------------------------------------------------------------------------------------------------------------------------------------------|
| id               |                                                                                                                                                  |
| order_id         |                                                                                                                                                  |
| purchasable_type | Morph reference for the purchasable item e.g. `product_variant`                                                                                  |
| purchasable_id   |
| type             | Whether `digital`,`physical` etc                                                            
| description      | A description of the line item                                                              
| option           | If this was a variant, the option info is here                                              
| identifier       | Something to identify the purchasable item, usually an `sku`                                
| unit_price       | The unit price of the line                                                                  
| unit_quantity    | The line unit quantity, usually this is 1                                                   
| quantity         | The amount of this item purchased                                                           
| sub_total        | The sub total minus any discounts, excl. tax                                                
| discount_total   | Any discount amount excl. tax                                                               
| tax_breakdown    | A json field for the tax breakdown e.g. `[{"description": "VAT", "identifier" : "vat", "value": 123, "percentage": 20, "currency_code": "GBP"}]`
| tax_total        | The total amount of tax applied                                                             
| total            | The grand total with tax                                                                    
| notes            | Any additional order notes                                                                  
| meta             | Any additional meta info you wish to store                                                  
| created_at       |                                                                                             |
| updated_at       |                                                                                             |


### Create an order line

::: tip
If you are using the `createOrder` method on a cart, this is all handled for you automatically.
:::

```php
\Lunar\Models\OrderLine::create([
    // ...
]);
```

Or via the relationship

```php
$order->lines()->create([
    // ...
]);
```

## Order Addresses

An order can have many addresses, typically you would just have one for billing and one for shipping.

::: tip
If you are using the `createOrder` method on a cart, this is all handled for you automatically.
:::

```php
\Lunar\Models\OrderAddress::create([
    'order_id' => 1,
    'country_id' => 1,
    'title' => null,
    'first_name' => 'Jacob',
    'last_name' => null,
    'company_name' => null,
    'line_one' => '123 Foo Street',
    'line_two' => null,
    'line_three' => null,
    'city' => 'London',
    'state' => null,
    'postcode' => 'NW1 1WN',
    'delivery_instructions' => null,
    'contact_email' => null,
    'contact_phone' => null,
    'type' => 'shipping', // billing/shipping
    'shipping_option' => null, // A unique code for you to identify shipping
]);

// Or via the relationship.
$order->addresses()->create([
    // ...
]);
```

You can then use some relationship helpers to fetch the address you need:

```php
$order->shippingAddress;
$order->billingAddress;
```

## Shipping Options

::: tip
A Shipping Tables addon is planned to make setting up shipping in the admin hub easy for most scenarios.
:::

To add Shipping Options you will need to [extend Lunar](/core/extending/shipping) to add in your own logic.

Then in your checkout, or where ever you want, you can fetch these options:

```php
\Lunar\Facades\ShippingManifest::getOptions(\Lunar\Models\Cart $cart);
```

This will return a collection of `Lunar\DataTypes\ShippingOption` objects.

### Adding the shipping option to the cart

Once the user has selected the shipping option they want, you will need to add this to the cart so it can calculate the
new totals.

```php
$cart->setShippingOption(\Lunar\DataTypes\ShippingOption $option);
```

## Transactions

```php
Lunar\Models\Transaction
```

| Field      | Description                                                                                   |
|:-----------|:----------------------------------------------------------------------------------------------|
| id         |                                                                                               |
| success    | Whether the transaction was successful                                                        |
| refund     | `true` if this was a refund                                                                   |
| driver     | The payment driver used e.g. `stripe`                                                         |
| amount     | An integer amount                                                                             |
| reference  | The reference returned from the payment Provider. Used to identify the transaction with them. 
| status     | A string representation of the status, unlinked to Lunar e.g. `settled`                       |
| notes      | Any relevant notes for the transaction                                                        
| card_type  | e.g. `visa`                                                                                   
| last_four  | Last 4 digits of the card                                                                     
| meta       | Any additional meta info you wish to store                                                    
| created_at |                                                                                               |
| updated_at |                                                                                               |

### Create a transaction

::: tip
Just because an order has a transaction does not mean it has been placed. Lunar determines whether an order is
considered placed when the `placed_at` column has a datetime, regardless if any transactions exist or not.
:::

Most stores will likely want to store a transaction against the order, this helps determining how much has been paid,
how it was paid and give a clue on the best way to issue a refund if needed.

```php
\Lunar\Models\Transaction::create([
    //...
]);

// Or via the order
$order->transactions()->create([
    //..
]);
```

These can then be returned via the relationship.

```php
$order->transactions; // Get all transactions.

$order->charges; // Get all transactions that are charges.

$order->refunds; // Get all transactions that are refunds.
```

## Payments

We will be looking to add support for the most popular payment providers, so keep an eye out here as we will list them
all out.

In the meantime, you can absolutely still get a storefront working, at the end of the day Lunar doesn't really mind what payment provider you use or plan to use.

In terms of an order, all it's worried about is whether or not the `placed_at` column is populated on the orders table,
the rest is completely up to you how you want to handle that. We have some helper utilities to make such things easier
for you as laid out above.

And as always, if you have any questions you can reach out on our Discord!

## Order Status

The `placed_at` field determines whether an Order is considered draft or placed. The Order model has two helper methods
to determine the status of an Order.

```php
$order->isDraft();
$order->isPlaced();
```

## Order Notifications

Lunar allows you to specify what Laravel mailers/notifications should be available for sending when you update an
order's status. These are configured in the `lunar/orders` config file and are defined like so:

```php
'statuses'     => [
    'awaiting-payment' => [
        'label' => 'Awaiting Payment',
        'color' => '#848a8c',
        'mailers' => [
            App\Mail\MyMailer::class,
            App\Mail\MyOtherMailer::class,
        ],
        'notifications' => [],
    ],
    // ...
],
```

Now when you update an order's status in the hub, you will have these mailers available if the new status
is `awaiting-payment`. You can then choose the email addresses which the email should be sent to and also add an
additional email address if required.

Once updated, Lunar will keep a render of the email sent out in the activity log so you have a clear history of what's
been sent out.

:::tip
These email notifications do not get sent out automatically if you update the status outside of the hub.
:::

### Mailer template

When building out the template for your mailer, you should assume you have access to the `$order` model. When the status
is updated this is passed through to the view data for the mailer, along with any additional content entered.
Since you may not always have additional content when sending out the mailer, you should check the existence first.

Here's an example of what the template could look like:

```blade
<h1>It's on the way!</h1>

<p>Your order with reference {{ $order->reference }} has been dispatched!</p>

<p>{{ $order->total->formatted() }}</p>

@if($content ?? null)
    <h2>Additional notes</h2>
    <p>{{ $content }}</p>
@endif

@foreach($order->lines as $line)
    <!--  -->
@endforeach
```

## Order Invoice PDF

By default when you click "Download PDF" in the hub when viewing an order, you will get a basic PDF generated for you to
download. You can publish the view that powers this to create your own PDF template.

```bash
php artisan vendor:publish --tag=lunar.hub.views
```

This will create a view called `resources/vendor/lunarpanel/pdf/order.blade.php`, where you will be able to freely
customise the PDF you want displayed on download.
