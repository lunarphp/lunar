# Extending the Plugin

Lunar's admin panel is registered as a Filament plugin behind the scenes. This provides flexibility for those developers
that want true control over everything.

::: warning
We do not suggest registering the plugin in your own Filament install unless absolutely necessary, as it will add 
reasonable complexity to your project.
:::

## Switching Resources

Lunar's plugin allows you to switch resource, page and widget classes with your own implementations.

```php
use Lunar\Admin\LunarPlugin;

LunarPlugin::switchResource(
    \Lunar\Panel\Filament\Resources\ProductResource::class,
    \App\Filament\Resources\ProductResource::class
);
```

```php
use Lunar\Admin\LunarPlugin;

LunarPlugin::switchPage(
    \Lunar\Panel\Filament\Pages\Collections::class,
    \App\Filament\Pages\Collections::class
);
```

```php
use Lunar\Admin\LunarPlugin;

LunarPlugin::switchWidget(
    \Lunar\Panel\Filament\Widgets\SalesReport::class,
    \App\Filament\Widgets\SalesReport::class
);
```
