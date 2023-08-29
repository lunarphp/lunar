# Search

## Overview

Good search is the backbone of any storefront so Lunar aims to make this as extensible as possible so you can index what
you need for your front-end, without compromising on what we require our side in the hub.

There are three things to consider when you want to extend the search:

- Searchable fields
- Sortable fields
- Filterable fields

## Default index values

Eloquent models which use the `Lunar\Base\Traits\Searchable` trait will use an indexer class to tell Scout how each it
should be indexed, if an indexer isn't mapped in the config the default `EloquentIndexer` (provided by Lunar) will be
used.

This class will map a basic set of fields to the search index:

- The ID of the model
- Any `searchable` attributes.

Some models require a bit more information to be indexed, such as SKU's, prices etc. For these scenarios, dedicated
indexers have been created and are mapped in the config already.

#### `Lunar\Search\ProductIndexer`

Fields which are indexed:

- The ID of the model
- Any `searchable` attributes.
- The product `status`
- The product `product_type`
- The `brand` (if applicable)
- The ProductVariant `skus` related to the product.
- The `created_at` timestamp

## Mapping custom indexers

All indexers are mapped in `config/search.php` under `indexers`, if a model isn't mapped here then it will
simply use the default `ELoquentIndexer`. To change how each model is indexed, simply map it like so:

```php
return [
    // ...
    'indexers' => [
        Lunar\Models\Product::class => App\Search\CustomProductIndexer::class,
    ],
],
```

## Creating a custom indexer

To create your own indexer, simply create a custom class like so:

```php
<?php

namespace App\Search;

use Lunar\Search\EloquentIndexer;

class CustomProductIndexer extends EloquentIndexer
{
    // Scout method to return the index name.
    public function searchableAs(Model $model): string
    {
        return 'custom_index_name';
    }
    
    // Scout method to return whether the model should be searchable.
    public function shouldBeSearchable(Model $model): bool
    {
        return true;
    }
    
    // Scout method to allow you to tap into eager loading.
    public function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->with([
            'thumbnail',
            'variants',
            'productType',
            'brand',
        ]);
    }
    
    // Scout method to get the ID used for indexing
    public function getScoutKey(Model $model): mixed
    {
        return $model->getKey();
    }
    
    // Scout method to get the column used for the ID.
    public function getScoutKeyName(Model $model): mixed
    {
        return $model->getKeyName();
    }
    
    // Simple array of any sortable fields.
    public function getSortableFields(): array 
    {
        return [
            'created_at',
            'updated_at',
        ];
    }
    
    // Simple array of any filterable fields.
    public function getFilterableFields(): array 
    {
        return [
            '__soft_deleted',
        ];
    }
    
    // Return an array representing what should be sent to the search service i.e. Algolia
    public function toSearchableArray(Model $model, string $engine): array
    {
        return array_merge([], $this->mapSearchableAttributes($model));
    }
}
```

The `EloquentIndexer` class implements the `Lunar\Search\Interfaces\ModelIndexerInterface` so if your class doesn't
extend the Eloquent one, you must implement this interface.

There are some methods which are available just on the `EloquentIndexer` but not defined on the interface are:

#### mapSearchableAttributes

```php
mapSearchableAttributes(Model $model): array
```

This method will take all `searchable` attributes for the model attribute type and map them into the index,
this means when you add searchable attributes in the hub they will automatically be added to the index.