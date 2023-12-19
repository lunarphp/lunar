# Extending The Panel

You may customise the Filament panel when registering it in your app service provider.

We provide a handy function which gives you direct access to the panel to change its properties.

## Customise panel properties
For example, the following would change the panel's URL to `/admin` rather than the default `/lunar`.

```php
use Lunar\Admin\Support\Facades\LunarPanel;

// ...

LunarPanel::panel(fn($panel) =>
    $panel->path('admin'))
    // ...
    ->register();
```

## Customise title & logo
For example, the following would change the panel's title or logo

```php
use Lunar\Admin\Support\Facades\LunarPanel;

// ...
LunarPanel::panel(fn($panel) => 
    $panel->brandName('My custom name')
    ->brandLogo('lunar-logo.svg'))
    // ...
    ->register();
```

## Customise theme
For example, the following would customise theme

```php
use Lunar\Admin\Support\Facades\LunarPanel;
use Filament\Support\Colors\Color;

// ...
LunarPanel::panel(fn($panel) =>
    $panel->colors([
            'danger' => Color::Rose,
            'gray' => Color::Gray,
            'info' => Color::Blue,
            'primary' => Color::Indigo,s
            'success' => Color::Emerald,
            'warning' => Color::Orange,
    s]);
     // ...
    ->register();
```

## Default configuration
The default configuration of Lunar panel is : 
```php
use Lunar\Admin\LunarPanelManager;

//....

Panel::make()
    ->default()
    ->id($this->panelId)
    ->brandName('Lunar')
    ->brandLogo($brandAsset('lunar-logo.svg'))
    ->darkModeBrandLogo($brandAsset('lunar-logo-dark.svg'))
    ->favicon($brandAsset('lunar-icon.png'))
    ->brandLogoHeight('2rem')
    ->path('lunar')
    ->authGuard('staff')
    ->defaultAvatarProvider(GravatarProvider::class)
    ->login()
    ->colors([
        'primary' => Color::Sky,
    ])
    // ...
```

Follow Filament documentation :

https://filamentphp.com/docs/3.x/panels/configuration