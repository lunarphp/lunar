# Model View/Preview URLs

It can be useful to provide a link to a product in order for a store admin to see how it will live prior to
it going live, or just to have a direct link to it. To enable this feature you will need to add a supporting class
to `config/lunar-hub/storefront.php`.

```php
<?php

namespace App\Storefront;

use Lunar\Models\Product;

class ProductUrls
{
    public function preview(Product $product)
    {
        if (!$product->defaultUrl) {
           return false;
        }
        return route('product.preview', $product->defaultUrl->slug, [
            'preview' => true,
        ]);
    }

    public function view(Product $product)
    {
        if (!$product->defaultUrl) {
           return false;
        }
        return route('product.view', $product->defaultUrl->slug);
    }
}
```

```php
'model_routes' => [
    // ...
    \Lunar\Models\Product::class => \App\Storefront\ProductUrls::class,
],
```
