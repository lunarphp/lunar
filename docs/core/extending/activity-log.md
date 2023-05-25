# Activity Log

## Overview

Over the course of your storefront's lifetime, various events will happen throughout the system. These range from generic model events which are captured by Lunar automatically, to custom events either triggered by add-ons or implemented yourself.

These events are then presented in the Admin Hub in a timeline, this is great for the generic events, but what if you have a custom event that you want to display a bit differently to the rest of the items in the timeline? Well we've got it covered...

## Adding a custom renderer

You can tell Lunar how to render certain activity log events, this is achieved by creating a render class and registering it with the Activity Log manifest:

```php
<?php

namespace App\ActivityLog;

use Lunar\Hub\Base\ActivityLog\AbstractRender;
use Spatie\Activitylog\Models\Activity;

class ActivityLogRenderer extends AbstractRender
{
    public function getEvent(): string
    {
        return 'created';
    }

    public function render(Activity $log)
    {
        return view('my-custom-activity-log-item', [
          'log' => $log
        ]);
    }
}
```

Then in our view we have access to the `ActivityLog` model, so for example this one for displaying a payment against an order:

```html
{{ __('adminhub::components.activity-log.orders.capture', [
    'amount' => price($log->getExtraProperty('amount'), $log->subject->currency)->formatted,
    'last_four' => $log->getExtraProperty('last_four'),
]) }}
```

::: tip
You don't need to worry about displaying the date or causer of the event, this will be handled by the timeline.
:::

We have our render class, by itself it doesn't do much, we need to tell Lunar where this should be rendered:

```php
ActivityLog::addRender(
  \Lunar\Models\Order::class,
  ActivityLogRenderer::class
);
```

The first parameter is the class name for the subject of the event and the second parameter is the class name to the renderer class we just created.
This gives us the flexibility to assign the same render across multiple models.

Now when we view the timeline for an `Order` and we have a `created` event, we will see our view rendered in the timeline.
