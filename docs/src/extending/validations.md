# Validations

[[toc]]

## Overview

If you want to add additional validation rules, you can do so by registering in service provider.

## Extending Validation Rules

```php
use Lunar\Hub\Http\Livewire\Components\Products\ProductCreate;
use Lunar\Models\Product;

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
 
| Type    | Page                                                                                                                         | Closure parameters               |
| ------- | ---------------------------------------------------------------------------------------------------------------------------- | -------------------------------- |
| Product | `\Lunar\Hub\Http\Livewire\Components\Products\ProductCreate`<br />`\Lunar\Hub\Http\Livewire\Components\Products\ProductShow` | `\Lunar\Models\Product $product` |
 