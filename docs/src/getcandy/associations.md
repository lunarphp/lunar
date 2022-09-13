# Associations

[[toc]]

## Overview

Associations allow you to relate products to each other. There are a few different ways you can associate two products and this type of relationship would define how they are presented on your storefront and also how Lunar sees them.

## Loading associations

```php
$product->associations
```

This will return a Laravel collection of `Lunar\Models\ProductAssociation` models. On each model you will have access to the following:

```php
// Lunar\Models\ProductAssociation
$association->parent; // The owning product who has the associations
$association->target // The associated (cross-sell, up-sell, alternate) product.
$association->type // Whether it's cross-sell, up-sell or alternate.
```

## Types of association

### Cross Sell

Cross selling is the process of encouraging customers to purchase products or services in addition to the original items they intended to purchase. Oftentimes the cross-sold items are complementary to one another so customers have more of a reason to purchase both of them.

For example, if you're selling a Phone on your store, you may want to present some headphones or a case that works with the phone which the customer may be interested in.

**Adding a cross-sell association**

```php
$product->associate(
    \Lunar\Models\Product $crossSellProduct,
    \Lunar\Models\ProductAssociation::CROSS_SELL
);

$product->associate(
    [$productA, $productB],
    \Lunar\Models\ProductAssociation::CROSS_SELL
);
```

**Fetching cross-sell products**

```php
// Via a relationship scope
$product->associations()->crossSell()->get();

// Via the type scope
$product->associations()->type(ProductAssociation::CROSS_SELL);
```

### Up Sell

Upselling is the process of encouraging customers to upgrade or include add-ons to the product or service they’re buying. The product or service being promoted is typically a more expensive product or add ons which can increase the overall order value.

Using the phone example from above, lets consider we have two phones.

- Phone 16gb 5" Screen
- Phone 32gb 6" Screen

When editing the 16gb Phone we would add the 32gb version as an up-sell association and could present this when a user is viewing the 16gb version.

```php
$product->associate(
    \Lunar\Models\Product $upSellProduct,
    \Lunar\Models\ProductAssociation::UP_SELL
);

$product->associate(
    [$productA, $productB],
    \Lunar\Models\ProductAssociation::UP_SELL
);
```

**Fetching up-sell products**

```php
// Via a relationship scope
$product->associations()->upSell()->get();

// Via the type scope
$product->associations()->type(ProductAssociation::UP_SELL);
```

### Alternate

Alternate products are what you could present the user as an alternative to the current product. This is helpful in situations where the product might be out of stock or not quite fit for purpose and you could show these.

```php
$product->associate(
    \Lunar\Models\Product $alternateProduct,
    \Lunar\Models\ProductAssociation::ALTERNATE
);

$product->associate(
    [$productA, $productB],
    \Lunar\Models\ProductAssociation::ALTERNATE
);
```

**Fetching alternate products**

```php
// Via a relationship scope
$product->associations()->alternate()->get();

// Via the type scope
$product->associations()->type(ProductAssociation::ALTERNATE);
```

### Custom types

Although Lunar comes preloaded with the associations above, you are free to add your own custom association types.

```php
$product->associate(
    \Lunar\Models\Product $alternateProduct,
    'my-custom-type'
);
```

You can then fetch all associated products like so:

```php
$product->associations()->type('my-custom-type')->get();
```

## Removing associations

You can dissociate products with one simple method. If you only pass through the related models, or an array of models, all associations will be removed. If you wish to only remove associations for a certain type you can pass the type through as the second parameter.

```php
// Remove this associated product from however many different association types it might have.
$product->dissociate($associatedProduct);

// Will also accept an array or collection of products.
$product->dissociate([/* ... */])

// Only remove this products association if it has been associated as a cross-sell.
$product->dissociate($associatedProduct, Product::CROSS_SELL);
```

## Database Schema

|Field|Description|
|:-|:-|
|`id`||
|`product_id`||
|`product_association_id`||
|`type`|(cross-sell, up-sell, alternate)|
|`created_at`||
|`updated_at`||
