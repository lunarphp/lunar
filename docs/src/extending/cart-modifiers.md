# Cart Modifiers

[[toc]]

## Overview

There may instances where you need to make changes to a cart or cart line, before and/or after calculations have taken place. For this GetCandy usees `Pipelines`. The cart/cart lines are pumped through these pipelines and you are free to make any changes you need either before or after calculation:

## Adding a Cart Modifier

```php
<?php

namespace App\Modifiers;

use GetCandy\Base\CartModifier;
use GetCandy\Models\Cart;

class CustomCartModifier extends CartModifier
{
    public function calculating(Cart $cart)
    {
        //
    }

    public function calculated(Cart $cart)
    {
        //
    }
}
```

```php
<?php

namespace App\Modifiers;

use GetCandy\Base\CartLineModifier;
use GetCandy\Models\CartLine;

class CustomCartLineModifier extends CartLineModifier
{
    public function calculating(CartLine $cartLine)
    {
        //
    }

    public function calculated(CartLine $cartLine)
    {
        //
    }
}
```

Then register your modifier in your service provider.

```php
public function boot(
    \GetCandy\Base\CartModifiers $cartModifiers,
    \GetCandy\Base\CartLineModifiers $cartLineModifiers
) {
    $cartModifiers->add(
        CustomCartModifier::class
    );

    $cartLineModifiers->add(
        CustomCartLineModifier::class
    );
}
```