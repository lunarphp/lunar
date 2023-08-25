# Search

## Overview

Good search is the backbone of any storefront so Lunar aims to make this as extensible as possible so you can index what
you need for your front-end, without compromising on what we require our side in the hub.

There are three things to consider when you want to extend the search:

- Searchable fields
- Sortable fields
- Filterable fields

### Default index values

Eloquent models will use an indexer class to tell Scout how each model should be indexed. Lunar ships with
an `EloquentIndexer` by default which will map the following:

- The ID of the model
- Any `searchable` attributes.

If you are using the `mysql` driver, then the model's `->toArray()` method will simply be called.

Some models, such as products, require a bit more information to be indexed and by default we have created extra index
classes as follows:

#### Products

The following additional fields are mapped for products:

- `status`
- `product_type`
- `brand` (if applicable)
- `created_at`

### Document mapping

All indexers are mapped in `config/search.php` under `document_indexers`, if a model isn't mapped here then it will
simply use the default `ELoquentIndexer`. To change how each model is indexed, simply map it like so:

```php
return [
    // ...
    'document_indexers' => [
        Lunar\Models\Product::class => App\Search\CustomProductIndexer::class,
    ],
],
```

### Creating a custom indexer

To create your own indexer, simply create a custom class like so:

```php
<?php

namespace App\Search;

use Lunar\Search\EloquentIndexer;

class CustomProductIndexer extends EloquentIndexer
{
    // ...
}
```

The `EloquentIndexer` class implements the `Lunar\Search\Interfaces\DocumentIndexerInterface` so if your class doesn't
extend the Eloquent one, you must implement this interface.
Lets take a look at the available methods, some will appear very familiar to what scout offers.

```php
public function searchableAs(Model $model): string;

public function shouldBeSearchable(Model $model): bool;

public function makeAllSearchableUsing(Builder $query): Builder;

public function getScoutKey(Model $model): mixed;

public function getScoutKeyName(Model $model): mixed;

public function getSortableFields(): array;

public function getFilterableFields(): array;

public function getDocument(Model $model, string $engine): array;
```