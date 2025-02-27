# Extending The Panel

You may customise the Filament panel when registering it in your app service provider.

We provide a handy function which gives you direct access to the panel to change its properties.

For example, the following would change the panel's URL to `/admin` rather than the default `/lunar`.

```php
use Lunar\Admin\Support\Facades\LunarPanel;

LunarPanel::panel(fn($panel) => $panel->path('admin'))
    ->extensions([
        // ...
    ])
    ->register();
```

## Adding to the panel

The [Filament](https://filamentphp.com/) panel allows you to add further screens in the form of Pages or Resources, and 
indeed customise any available panel option.

Below is an example of how you can use the panel object.

```php
LunarPanel::panel(function ($panel) {
    return $panel
        ->pages([
            // Register standalone Filament Pages
            SalesReport::class,
            RevenueReport::class,
        ])
        ->resources([
            // Register new Filament Resources
            OpeningTimeResource::class,
            BannerResource::class,
        ])
        ->livewireComponents([
            // Register Livewire components
            OrdersSalesChart::class,
        ])->plugin(
            // Register a Filament plugin
            new ShippingPlugin(),
        )
        ->navigationGroups([
            // Set the navigation groups
            'Catalog',
            'Sales',
            'CMS',
            'Reports',
            'Shipping',
            'Settings',
        ]);
})->register();
```
For further information please consult the [Filament documentation](https://filamentphp.com/docs).
