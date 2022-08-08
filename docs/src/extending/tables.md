# Tables

[[toc]]

## Overview

Across the hub tables are used to display lists of data that exist in the database. For this we use the [Filmentphp tables builder](https://filamentphp.com/docs/2.x/tables/installation) as this allows developers to tap into and extend tables with minimal effort.

If you're unfamiliar with the table package by Filamentphp, it's worth a read to see exactly what you can do in GetCandy.

On top of everything that Filament offers, we also have added some helpers so you can fully extend the tables which are available in GetCandy.


### Available tables

- `\GetCandy\Hub\Http\Livewire\Components\Tables\ChannelsTable`
- `\GetCandy\Hub\Http\Livewire\Components\Tables\CurrenciesTable`
- `\GetCandy\Hub\Http\Livewire\Components\Tables\ProductsTable`
- `\GetCandy\Hub\Http\Livewire\Components\Tables\ProductTypesTable`
- `\GetCandy\Hub\Http\Livewire\Components\Tables\OrdersTable`
- `\GetCandy\Hub\Http\Livewire\Components\Tables\StaffTable`

### Adding Columns

```php
OrdersTable::addColumn(
    column: \Filament\Tables\Columns\TextColumn::make('custom_column'),
    after: 'id',
);
```

See [Filamentphp Columns](https://filamentphp.com/docs/2.x/tables/columns#getting-started) for more information

### Replacing Columns

If instead of adding extra columns, you can completely change what columns are shown on the table.

```php
OrdersTable::setColumns([
    \Filament\Tables\Columns\TextColumn::make('column_a'),
    \Filament\Tables\Columns\TextColumn::make('column_b'),
    \Filament\Tables\Columns\TextColumn::make('column_c'),
]);
```

### Adding Filters

```php
OrdersTable::addFilter(
    \Filament\Tables\Filters\Filter::make('status')
);
```

See [Filamentphp Filters](https://filamentphp.com/docs/2.x/tables/filters#getting-started) for more information.

### Adding Actions

```php
OrdersTable::addAction(
    \Filament\Tables\Actions\Action::make('myAction')
);
```

See [Filamentphp Actions](https://filamentphp.com/docs/2.x/tables/actions#getting-started) for more information.

### Adding Bulk Actions

```php
OrdersTable::addBulkAction(
    \Filament\Tables\Actions\Action::make('myAction')
);
```

See [Filamentphp Actions](https://filamentphp.com/docs/2.x/tables/actions#getting-started) for more information
