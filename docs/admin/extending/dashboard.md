# Extending The Dashboard

You may customise the Lunar Dashboard when registering it in your app service provider.

```php
<?php

namespace App\Filament\Extensions;

use Lunar\Admin\Support\Extending\BaseExtension;

class DashboardExtension extends BaseExtension
{
    /**
    * Override or add to all widgets on the dashboard
    */
    public function getWidgets(array $widgets): array
    {
        return [
            //...
        ];
    }
    
    /**
    * Override or add to the overview widgets at the top of the dashboard
    */
    public function getOverviewWidgets(array $widgets): array
    {
        return [
            //...
        ];
    }
    
    /**
    * Override or add to the chart widgets
    */
    public function getChartWidgets(array $widgets): array
    {
        return [
            //...
        ];
    }
    
    /**
    * Override or add to the table widgets
    */
    public function getTableWidgets(array $widgets): array
    {
        return [
            //...
        ];
    }
}

```

```php
public function boot()
{
    \Lunar\Admin\Support\Facades\LunarPanel::extensions([
        \Lunar\Admin\Filament\Pages\Dashboard::class => App\Filament\Extensions\DashboardExtension::class,
    ]);
}
```
