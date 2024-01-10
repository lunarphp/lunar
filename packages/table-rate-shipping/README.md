# Lunar Table Rate Shipping


# Requirements

- LunarPHP Admin `>` `1.x`

# Installation

Install via Composer

```
composer require lunarphp/table-rate-shipping
```

Then register the plugin in your service provider

```php
use Lunar\Admin\Support\Facades\LunarPanel;
use Lunar\Shipping\ShippingPlugin;
// ...

public function register(): void
{
    LunarPanel::panel(function (Panel $panel) {
        return $panel->plugin(new ShippingPlugin());
    })->register();
    
    // ...
}
```
# Getting Started
This addon provides an easy way for you to add different shipping to your storefront and allow your customers to choose from the different shipping rates you have set up, based on various factors such as zones, minimum spend etc

## Shipping Methods

Shipping Methods are the different ways in which your storefront can send orders to customers, you could also allow customers to collect their order from your store which this addon supports.

## Shipping Zones

Shipping Zones allow you to section of area's of the countries you ship to, providing you with an easy way to offer distinct shipping methods and pricing to each zone, each zone can be restricted by the following:

- Postal codes
- Country
- State/Province (based on country)

## Shipping Rates

Shipping Rates are the prices you offer for each of your shipping zones, they are linked to a shipping method. So for example you might have a Courier Area Shipping Zone and an Everywhere Else Shipping Zone, you can offer different pricing restrictions using the same shipping methods.

## Shipping Exclusion Lists

Sometimes, you might not want to ship certain items to particular Shipping Zone, this is where exclusion lists come in. You can associate purchasables to a list which you can then associate to a shipping zone, if a cart contains any of them then they won't be able to select a shipping rate.

# Storefront usage
