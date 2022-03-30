# Admin Hub

[[toc]]

## Overview

The admin hub is designed to be extended so you can add your own screens.

You should develop your additional functionality using Laravel Livewire using the same approach as the core admin hub screens.

## Adding to Menus

GetCandy uses dynamic menus in the UI which you can extend to add further links.

::: tip
Currently, only the side menu and settings menu are available to extend. But we will be adding further menus into the core editing screens soon.
:::

Here is an example of how you would add a new link to the side menu.

```php
use GetCandy\Hub\Facades\Menu;

$slot = Menu::slot('sidebar');

$slot->addItem(function ($item) {
    $item->name(
        __('menu.sidebar.tickets')
    )->handle('hub.tickets')
    ->route('hub.tickets.index')
    ->icon('ticket');
});
```

GetCandy comes with a collection of icons you can use in the Resources folder. If you wish to supply your own, simply use an SVG instead, e.g.

```php
->icon('<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" stroke-width="1.5" stroke="#9A9AA9" fill="none" stroke-linecap="round" stroke-linejoin="round">
  <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
  <line x1="15" y1="5" x2="15" y2="7" />
  <line x1="15" y1="11" x2="15" y2="13" />
  <line x1="15" y1="17" x2="15" y2="19" />
  <path d="M5 5h14a2 2 0 0 1 2 2v3a2 2 0 0 0 0 4v3a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-3a2 2 0 0 0 0 -4v-3a2 2 0 0 1 2 -2" />
</svg>');
```

## Customising Edit Screens

Currently there is no way to add additional fields or components to editing screens. However, we intend to look into adding a "slots" feature to enable just that.

## Customising Tables

Throughout GetCandy there are a number of data tables on pages, such as product, orders etc. We want to make these flexible and allow you to extend them by adding functionality such as additional columns and filters.

We'll be working towards adding this functionality across as many data tables as possible, but for now the supported tables are:

- `GetCandy\Facades\OrdersTable`

### Adding Columns

The signature for adding a column is below, the closure will receive and instance of the `Model` for that row.

```php
addColumn(string $header, bool $sortable = false, Closure $callback = null): TableColumn
```

```php
OrdersTable::addColumn('Delivery Area', false, function (Order $order) {
  return 'Worldwide';
});
```

### Adding Filters

The signature for adding a filter is below, the closure will receive the value of the filter when looping through the available options. e.g. If we're filtering by `status` we'd receive `awaiting-payment`. Whatever is returned from the closure will be the value in the dropdown.

```php
addFilter(string $header, string $attribute, Closure $formatter = null): TableFilter
```

::: warning
The column should be an attribute that appears in the search index. For example if you wanted to filter on `status`
then that attribute must be indexed in either Meilisearch or Algolia and be enabled for filtering.
:::

```php
OrdersTable::addFilter('Status', 'status', function ($value) {
  return Str::slug($value);
});
```

### Exporting Records

GetCandy comes with basic exporter for each supported table. You're free to add your own, here's what it could look like:

```php
<?php

namespace App\Exporters;

use GetCandy\Models\Order;
use Illuminate\Support\Facades\Storage;

class OrderExporter
{
    /**
     * Export the orders.
     *
     * @param  array  $orderIds
     * @return void
     */
    public function export($orderIds)
    {
        $data = [$this->getHeadings()];

        $orders = Order::findMany($orderIds)->map(function ($order) {
            return collect([
                $order->id,
                $order->status,
                $order->reference,
                $order->billingAddress->full_name,
                $order->total->decimal,
                $order->created_at->format('Y-m-d'),
                $order->created_at->format('H:ma'),
            ])->join(',');
        })->toArray();

        $data = collect(array_merge($data, $orders))->join("\n");

        Storage::put('order_export.csv', $data);

        return Storage::download('order_export.csv');
    }

    /**
     * Return the csv headings.
     *
     * @return string
     */
    public function getHeadings()
    {
        return collect([
            'ID',
            'Status',
            'Reference',
            'Customer',
            'Total',
            'Date',
            'Time',
        ])->join(',');
    }
}
```

Then just tell the table to use it:

```php
OrdersTable::exportUsing(OrderExporter::class);
```