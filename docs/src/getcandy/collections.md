# Collections

[[toc]]

## Overview

Collections, although not strictly the same, are akin to Categories. They serve to allow you to add products ,either explicitly or via certain criteria, for use on your store.

For example, you may have a Collection called "Red T-Shirts" and within that collection specify that any product which has the tag "Red" and "T-Shirt" to be included.

A collection can also have other collections underneath it, forming a nested set hierarchy.

A collection must also belong to a collection group, this allows you to have greater flexibility when building out things like Menu's and Landing pages.


## Collection Groups

Create a collection group

```php
$group = \GetCandy\Models\CollectionGroup::create([
    'name' => 'Main Catalogue',
    'handle' => 'main-catalogue' // Will auto generate if omitted.
]);
```


## Collections

Collections are a hierarchy of models that have products associated to them, you can think of them as "Categories". Once you have added products you can then determine how they are sorted on display.

### Create a collection

```php
$collection = \GetCandy\Models\Collection::create([
    'attribute_data' => [
        'name' => new \GetCandy\FieldTypes\Text('Clearance'),
    ],
    'collection_group_id' => $group->id,
]);
```


### Add a child collection

```php
$child = new \GetCandy\Models\Collection::create([/*..*/]);

$collection->appendNode($child);
```

This results in the following

```bash
- Clearance
    - Child
```

GetCandy uses the [Laravel Nested Set](https://github.com/lazychaser/laravel-nestedset) package, so feel free to take a look at it to see what's possible.

### Adding products

Products are related using a `BelongsToMany` relationship with a pivot column for `position`.

```php
$products = [
    1 => [
        'position' => 1,
    ],
    2 => [
        'position' => 2,
    ]
];

$collection->products()->sync($products);
```

::: tip
The key in the `$products` array is the product id
:::

### Sorting products

GetCandy comes with a handful of criteria out the box for sorting products in a collection:

|Name|Description|
|:-|:-|
|`min_price:asc`|Sorts using the base price ascending|
|`min_price:desc`|Sorts using the base price descending|
|`sku:asc`|Sorts using the sku ascending|
|`sku:desc`|Sorts using the sku descending|
|`custom`|This will allow you to specify the order of each product manually|

Depending on what you have as the sort time on the collection, GetCandy will automatically sort the products for you when you update the products.
