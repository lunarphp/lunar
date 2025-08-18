# Introduction

Lunar's admin panel is powered by **Filament v3**. It allows you to easily extend the admin panel to suit your project.

With the panel you can administer your products, collections, orders, customers, discounts, settings and much more.

## Registering

If you followed the core installation instructions or have installed a starter kit, you will likely already have this in place.

```php
use Lunar\Admin\Support\Facades\LunarPanel;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        LunarPanel::register();
    }
```

## Contributing

If you wish to contribute to the project, please review the roadmap at https://github.com/orgs/lunarphp/projects/8/views/8

You can request to contribute on an issue in the backlog, or you can propose a new issue.

::: tip
Here's a guide on how to set-up your development environment ready for contributing to Lunar.
[Setting Up Lunar For Local Development](/core/local-development)
:::
