# Validations

[[toc]]

## Overview

If you want to add additional validation rules, you can do so by registering in service provider.

## Extending Validation Rules

```php
use GetCandy\Hub\Http\Livewire\Components\Products\ProductCreate;
use GetCandy\Models\Product;

public function boot() {
    ProductCreate::extendValidation([
        'variant.sku' => ['required', 'min:8'],
        'collections' => ['required', 'array', function (Product $product) {
            return function ($attribute, $value, $fail) use (Product $product) {
                // closure validation
                $fail($product->translateAttribute('name') . " validation failed");
            };
        }],
    ]);
}
```
 
| Type    | Page                                                                                                                               | Closure parameters                  |
| ------- | ---------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------- |
| Product | `\GetCandy\Hub\Http\Livewire\Components\Products\ProductCreate`<br />`\GetCandy\Hub\Http\Livewire\Components\Products\ProductShow` | `\GetCandy\Models\Product $product` |
 