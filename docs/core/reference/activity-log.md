# Activity Log

## Overview

We made a design choice to include activity logging throughout Lunar to record changes made on Eloquent models. We 
believe it is important to keep track of what updates are occurring and who is making them. It allows us to provide you 
with an invaluable insight into what is happening in your store.

## How It Works

For the actual logging, we opted to use the incredible package by Spatie, 
[laravel-activitylog](https://spatie.be/docs/laravel-activitylog). This allows Lunar to track changes throughout 
the system so you can have a full history of what's going on.

## Enabled Models
The following models have activity logging enabled by default in Lunar.

- `\Lunar\Models\Channel`
- `\Lunar\Models\Brand`
- `\Lunar\Models\Cart`
- `\Lunar\Models\CartAddress`
- `\Lunar\Models\CartLine`
- `\Lunar\Models\Currency`
- `\Lunar\Models\Order`
- `\Lunar\Models\OrderAddress`
- `\Lunar\Models\OrderLine`
- `\Lunar\Models\Product`
- `\Lunar\Models\ProductVariant`
- `\Lunar\Models\Transaction`


## Enabling On Your Own Models

If you want to enable logging on your own models you can simply 
[follow the guides on their website](https://spatie.be/docs/laravel-activitylog).
