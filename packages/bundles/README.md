# Lunar Bundles

Sell a set of product variants as one product. A bundle is an ordinary `ProductVariant` that carries a component list: stock is derived from the components, the price is either fixed or derived from the components, and the order records one component line per part so fulfilment and stock commitment work unchanged.

```php
$bundle = app(DefinesBundle::class)->execute($variant, BundlePricing::Components, discountPercentage: 10);

$bundle->syncComponents([
    ['variant' => $body, 'quantity' => 1],
    ['variant' => $bag, 'quantity' => 1],
]);

CartSession::add($variant, 1);
```

See spec `0085-product-bundles` in the monorepo for the full design.
