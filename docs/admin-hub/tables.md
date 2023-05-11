# Tables

## Overview

Tables are the backbone of the hub and how data is displayed. Therefore it's important they can be extended when needed and also allow you to create your own tables when developing add-ons.

All tables use a `TableBuilder` class to add columns, filters and actions. It's these classes that can be extended in order to provider a richer experience in the hub when displaying data.

## Available builders

- `Lunar\Hub\Tables\Builders\CustomersTableBuilder`
- `Lunar\Hub\Tables\Builders\OrdersTableBuilder`
- `Lunar\Hub\Tables\Builders\ProductsTableBuilder`
- `Lunar\Hub\Tables\Builders\ProductTypesTableBuilder`
- `Lunar\Hub\Tables\Builders\ProductVariantsTableBuilder`

## Getting the builder instance

You can resolve each builder class as a singleton from the container. Generally this can be done via a service provider:

```php
use Lunar\Hub\Tables\Builders\ProductsTableBuilder;

public function boot(ProductsTableBuilder $productTableBuilder)
{
    // ...
}
```

## Adding columns

```php
$productsTableBuilder->addColumn($columnClass);
```

When adding columns, there is some flexibility on how they are rendered.

#### Standard

This is the default behaviour and will use the view provided by Lunar.

#### Laravel Component

You can render your own Laravel component

```php
TextColumn::make('status')->viewComponent('my-column');
```

This will pass through the instance of the row so it's available:

```php
<?php

namespace App\Views;

use Illuminate\View\Component;

class MyColumn extends Component
{
    public function __construct($record)
    {
        // ...
    }

    // ...
}
```

#### Livewire Component

```php
TextColumn::make('status')->livewire('livewire.component.reference');
```

When rendered, the current record will be passed through via a prop.

```php

<?php

namespace App\Http\Livewire\Components;

use Livewire\Component;
use Illuminate\Database\Eloquent\Model;

class CustomColumnComponent extends Component
{
    public Model $record;

    // ...
}
```

### Positioning

You can specify a position for the new column by defining which column it should appear after.

```php
TextColumn::make('status')->after('name');
```

### TextColumn

```php
use Lunar\LivewireTables\Components\Columns\TextColumn;

// Specify an existing column
$productsTableBuilder->addColumn(
    TextColumn::make('status')
);

// Reference a relationship
$productsTableBuilder->addColumn(
    TextColumn::make('productType.name')
);

// Use a callback to return the value for each row
$productsTableBuilder->addColumn(
    TextColumn::make('store', function ($product) {
        return $product->store;
    })->heading('Store')
);
```


### BadgeColumn

```php
use Lunar\LivewireTables\Components\Columns\BadgeColumn;

BadgeColumn::make('status', function ($record) {
    return $record->status;
})->states(function ($record) {
    return [
        'success' => $record->status == 'published' && ! $record->deleted_at,
        'warning' => $record->status == 'draft' && ! $record->deleted_at,
        'danger' => (bool) $record->deleted_at,
    ];
});
```


### ImageColumn

```php
use Lunar\LivewireTables\Components\Columns\ImageColumn;

ImageColumn::make('thumbnail', function ($record) {
    return $record->thumbnail->getUrl('small');
})->heading(false);
```

:::tip
Calling `heading(false)` will prevent heading text from being displayed.
:::

### AvatarColumn

```php
use Lunar\LivewireTables\Components\Columns\AvatarColumn;

AvatarColumn::make('avatar', function ($record) {
    return $record->email;
})->gravatar()->heading(false);
```

By calling `gravatar()` we're telling the column to convert it to a hash that Gravatar can understand and render.

### StatusColumn

```php
use Lunar\LivewireTables\Components\Columns\StatusColumn;

StatusColumn::make('active', function ($record) {
    return ! $record->deleted_at;
});
```

## Adding Filters

```php
$productsTableBuilder->addFilter($filterClass);
```


### SelectFilter

```php
use Lunar\LivewireTables\Components\Filters\SelectFilter;

SelectFilter::make('status')->options(function () {
    return collect([
        null => 'All Statuses',
        'payment-received' => 'Payment Received',
    ])->merge($statuses);
})->query(function ($filters, $query) {
    $value = $filters->get('status');

    if ($value) {
        $query->whereStatus($value);
    }
});
```


### DateFilter

```php
use Lunar\LivewireTables\Components\Filters\DateFilter;

DateFilter::make('placed_at')
    ->heading('Placed at')
    ->query(function ($filters, $query) {
        $value = $filters->get('placed_at');

        if (! $value) {
            return $query;
        }

        $parts = explode(' to ', $value);

        if (empty($parts[1])) {
            return $query;
        }

        $query->whereBetween('placed_at', [
            $parts[0],
            $parts[1],
        ]);
    });
```

## Actions

Single actions will populate a dropdown on each row that will display the registered actions when clicked.

```php
$productsTableBuilder->addAction($actionClass);
```

### Action

```php
use Lunar\LivewireTables\Components\Actions\Action;

Action::make('view')->label('View Order')->url(function ($record) {
    return route('hub.orders.show', $record->id);
});
```

## Bulk Actions

Bulk actions are available when one or more rows are selected on the table. When an action is clicked the underlying Livewire component will be called with the selected ID's.

```php
$productsTableBuilder->addAction($actionClass);
```

### Bulk Action

```php
BulkAction::make('update_status')
    ->label('Update Status')
    ->livewire('hub.components.tables.actions.update-status')
```

The underlying Livewire component

```php
<?php

namespace Lunar\Hub\Http\Livewire\Components\Tables\Actions;

use Livewire\Component;
use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Models\Order;

class UpdateStatus extends Component
{
    use Notifies;

    /**
     * The array of selected IDs
     *
     * @var array
     */
    public array $ids = [];

    public $status = null;

    /**
     * {@inheritDoc}
     */
    public function getListeners()
    {
        return [
            'table.selectedRows' => 'setSelected',
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function rules()
    {
        return [
            'status' => 'required',
        ];
    }

    public function getStatusesProperty()
    {
        return config('lunar.orders.statuses');
    }

    /**
     * Set the selected ids
     *
     * @param  array  $rows
     * @return void
     */
    public function setSelected(array $rows)
    {
        $this->ids = $rows;
    }

    /**
     * Save the updated status
     *
     * @return
     */
    public function updateStatus()
    {
        Order::whereIn('id', $this->ids)->update([
            'status' => $this->status,
        ]);

        $this->notify('Order statuses updated');
        $this->emit('bulkAction.complete');
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.tables.actions.update-status')
            ->layout('adminhub::layouts.base');
    }
}
```

## Customising the query

If you add custom columns which reference relations, or want to make some performance improvements, it can be useful to customise the underlying query.

```php
$productsTableBuilder->extendQuery(function ($query) {
    $query->withCount('variants');
});
```


## Creating a new table

If you need to create a table for your own add-on, simply create a Livewire component which extends the Table component.

### Table component

```php
<?php

namespace App\Http\Livewire;

use Lunar\LivewireTables\Components\Table;

class OrdersTable extends Table
{
    /**
     * {@inheritDoc}
     */
    public function build()
    {
        $this->tableBuilder->baseColumns([
            TextColumn::make('id'),
        ]);
    }
    
    /**
     * Return the search placeholder.
     *
     * @return string
     */
    public function getSearchPlaceholderProperty(): string
    {
        return 'Search by keyword';
    }
    
    /**
     * {@inheritDoc}
     */
    public function getData()
    {
        return Mode::get();
    }
}
```

### Table builder class

Out the box, the table will be loaded with a base TableBuilder class. In most cases this should be enough, however you are free to add your own if needed.

#### Create a new table builder class

```php
<?php

namespace App\Tables;

use Lunar\Hub\Tables\TableBuilder;

class CustomTableBuilder extends TableBuilder
{
    // ...
}
```

Then update the reference on your Table component.

```php
<?php

namespace App\Http\Livewire;

use Lunar\LivewireTables\Components\Table;

class OrdersTable extends Table
{
    /**
     * The binding to use when building out the table.
     *
     * @var string
     */
    protected $tableBuilderBinding = CustomTableBuilder::class;
    
    // ...
}
```
