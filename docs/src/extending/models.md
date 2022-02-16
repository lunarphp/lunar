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
