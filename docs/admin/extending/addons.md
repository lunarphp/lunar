# Developing Add-ons

When creating add-on packages for Lunar you may wish to add new screens and functionality to the Filament panel.

To achieve this you will want to create a Filament plugin in your package. With Filament plugins you can add additional
resources, pages and widgets. See https://filamentphp.com/docs/3.x/panels/plugins for more information.

## Registering Filament plugins

To register a plugin in the Lunar admin panel you will want to do something similar to the below example.

```php
use Lunar\Admin\Support\Facades\LunarPanel;

LunarPanel::panel(fn($panel) => $panel->plugin(new ReviewsPlugin()))
    ->register();
```
