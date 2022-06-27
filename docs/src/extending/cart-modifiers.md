# Cart Modifiers

[[toc]]

## Overview

There may instances where you need to make changes to a cart or cart line, before and/or after calculations have taken place. For this GetCandy uses `Pipelines`. The cart/cart lines are pumped through these pipelines and you are free to make any changes you need either before or after calculation:

## Adding a Cart Modifier

```php
<?php

namespace App\Modifiers;

use Closure;
use GetCandy\Base\CartModifier;
use GetCandy\Models\Cart;

class CustomCartModifier extends CartModifier
{
    public function calculating(Cart $cart, Closure $next): Cart
    {
        // ...
        return $next($cart);
    }

    public function calculated(Cart $cart, Closure $next): Cart
    {
        // ...
        return $next($cart);
    }
}
```

```php
<?php

namespace App\Modifiers;

use Closure;
use GetCandy\Base\CartLineModifier;
use GetCandy\Models\CartLine;

class CustomCartLineModifier extends CartLineModifier
{
    public function calculating(CartLine $cartLine, Closure $next): CartLine
    {
        // ...
        return $next($cartLine);
    }

    public function calculated(CartLine $cartLine, Closure $next): CartLine
    {
        // ...
        return $next($cartLine);
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
