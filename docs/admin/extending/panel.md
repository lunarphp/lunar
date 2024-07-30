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
