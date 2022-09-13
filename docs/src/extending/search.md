# Search

[[toc]]

## Overview

Good search is the backbone of any storefront so GetCandy aims to make this as extensible as possible so you can index what you need for your front-end, without compromising on what we require our side in the hub.

There are three things to consider when you want to extend the search:

- Searchable fields
- Sortable fields
- Filterable fields

Each of these can be extended using Model Observers in Laravel. The following models can be extended:

- `Lunar\Models\Collection`
- `Lunar\Models\Customer`
- `Lunar\Models\Order`
- `Lunar\Models\Product`
- `Lunar\Models\ProductOption`

## Creating and using an Observer

As mentioned, you simply need to add a [Model Observer](https://laravel.com/docs/9.x/eloquent#observers) for what you want to extend.

```php
<?php

namespace App\Observers;

use Lunar\Models\Order;

class OrderObserver
{
    /**
     * Called when we're about to index the order
     **/
    public function indexing(Order $order)
    {
        $order->addSearchableAttribute(
            'custom_field',
            $order->meta->custom_field
        );
    }

    /**
     * Called when we are setting up the index via
     * php artisan getcandy:meilisearch:update
     * */
    public function searchSetup(Order $order)
    {
        $order->addFilterableAttributes([
            'custom_field'
        ]);

        $order->addSortableAttributes([
            'custom_field'
        ]);
    }
}
```

You can then use these fields in your search:

```php
Product::search('Foo')->where('custom_field', 'Bar');
```
