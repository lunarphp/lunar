# Assets

[[toc]]

## Overview

When you are developing a package for the admin hub, it comes in handy to be able to register your own scripts and styles.

You can register your compiled assets as well as external ones. 
It is recommended to register compiled scripts as it limits the possibility of external scripts not loading.

## Registering Package Assets

Here is an example of how you would register assets in the `boot` method of your package service provider.

These assets will get registered to the admin hubs' `app` layout.

```php
use Lunar\Hub\LunarHub;

/**
 * Bootstrap the application services.
 */
public function boot()
{
    // ...

    // Register compiled script
    LunarHub::script('lunar-package', __DIR__.'/../dist/lunar-package.js');

    // Register remote script
    LunarHub::remoteScript('https://example.com/script.js');

    // Register compiled styles
    LunarHub::style('lunar-package', __DIR__.'/../dist/lunar-package.css');

    // Register remote styles
    LunarHub::remoteStyle('https://example.com/style.css');

    // ...
}

```
