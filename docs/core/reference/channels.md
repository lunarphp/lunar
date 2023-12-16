# Channels

## Overview

Default `webstore`.

## Assigning channels to models.

You can assign Eloquent models to different channels and specify whether they are enabled permanently or whether they should be scheduled to be enabled.

In order to add this kind of functionality to your model, you need to add the `HasChannels` trait.

```php
<?php

namespace Lunar\Traits\HasChannels;
// ...

class Product extends Model
{
    use HasChannels;
}
```

When add this trait, you will have access to the `scheduleChannel` method:

```php
$channel = App\Models\Channel::first();

// Will schedule for this product to be enabled in 14 days for this channel.
// and will be disabled after 24 days
$product->scheduleChannel($channel, now()->addDays(14), now()->addDays(24));

// Schedule the product to be enabled straight away
$product->scheduleChannel($channel);

// The schedule method will accept and array or collection of channels.
$product->scheduleChannel(Channel::get());
```

There is also a channel scope available to models which use this trait:

```php
// Limit to a single channel
Product::channel($channel)->get();

// Limit to multiple channels
Product::channel([$channelA, $channelB])->get();

// Limit to a channel available the next day
Product::channel($channelA, now()->addDay())->get();

// Limit to a channel within a date range.
Product::channel($channelA, now()->addDay(), now()->addDays(2))->get();
```
