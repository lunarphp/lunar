# Livewire Tables

## Installation

```
composer require getcandy/livewire-tables
```

## Creating a table

```php
<?php

namespace App\Http\Livewire\Tables;

use GetCandy\LivewireTables\Components\Table;

class OrdersTable extends Table
{

}
```

## Features

- [Searching](#searching)

### Searching

To enable searching on the table, set the `$searchable` property to `true`.

```php
/**
 * Whether this table is searchable.
 *
 * @var bool
 */
public $searchable = true;
```

This will enable a search box and add the `query` parameter whenever the search input is used.
