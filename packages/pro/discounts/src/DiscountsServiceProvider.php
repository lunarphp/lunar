<?php

namespace GetCandy\Discounts;

// use GetCandy\Base\ShippingModifiers;
// use GetCandy\Hub\Auth\Manifest;
// use GetCandy\Hub\Auth\Permission;
// use GetCandy\Hub\Facades\Menu;
// use GetCandy\Shipping\Http\Livewire\Components\ShippingMethods\Collection;
// use GetCandy\Shipping\Http\Livewire\Components\ShippingMethods\FlatRate;
// use GetCandy\Shipping\Http\Livewire\Components\ShippingMethods\FreeShipping;
// use GetCandy\Shipping\Http\Livewire\Components\ShippingMethods\ShipBy;
// use GetCandy\Shipping\Http\Livewire\Pages\ShippingExclusionListsCreate;
// use GetCandy\Shipping\Http\Livewire\Pages\ShippingExclusionListsIndex;
// use GetCandy\Shipping\Http\Livewire\Pages\ShippingExclusionListsShow;
// use GetCandy\Shipping\Http\Livewire\Pages\ShippingIndex;
// use GetCandy\Shipping\Http\Livewire\Pages\ShippingZoneCreate;
// use GetCandy\Shipping\Http\Livewire\Pages\ShippingZoneShow;
// use GetCandy\Shipping\Interfaces\ShippingMethodManagerInterface;
// use GetCandy\Shipping\Managers\ShippingManager;
// use GetCandy\Shipping\Menu\ShippingMenu;

use GetCandy\Base\CartLineModifiers;
use GetCandy\Base\CartModifiers;
use GetCandy\Discounts\Interfaces\DiscountConditionManagerInterface;
use GetCandy\Discounts\Interfaces\DiscountRewardManagerInterface;
use GetCandy\Discounts\Managers\DiscountConditionManager;
use GetCandy\Discounts\Managers\DiscountRewardManager;
use GetCandy\Discounts\Modifiers\DiscountCartModifier;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class DiscountsServiceProvider extends ServiceProvider
{
    public function boot(
        CartModifiers $cartModifiers
    ) {

        $this->app->singleton(DiscountConditionManagerInterface::class, function ($app) {
            return $app->make(DiscountConditionManager::class);
        });

        $this->app->singleton(DiscountRewardManagerInterface::class, function ($app) {
            return $app->make(DiscountRewardManager::class);
        });

        $cartModifiers->add(
            DiscountCartModifier::class
        );


//         $this->loadTranslationsFrom(__DIR__.'/../lang', 'shipping');
//
//         $slot = Menu::slot('sidebar');
//
//         $slot->addItem(function ($item) {
//             $item->name(
//                 __('shipping::index.menu_item')
//             )->handle('hub.shipping')
//             ->route('hub.shipping.index')
//             ->icon('truck');
//         });
//
//         $shippingModifiers->add(
//             ShippingModifier::class
//         );
//
//         $this->loadRoutesFrom(__DIR__.'/../routes/hub.php');
//
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
//
//         $this->loadViewsFrom(__DIR__.'/../resources/views', 'shipping');
//
//         // // Register the stripe payment component.
//
//         $components = [
//             // Pages
//             ShippingExclusionListsIndex::class,
//             ShippingExclusionListsCreate::class,
//             ShippingExclusionListsShow::class,
//             ShippingIndex::class,
//             ShippingZoneShow::class,
//             ShippingZoneCreate::class,
//
//             // Shipping Methods
//             FreeShipping::class,
//             FlatRate::class,
//             ShipBy::class,
//             Collection::class,
//         ];
//
//         foreach ($components as $component) {
//             Livewire::component((new $component())->getName(), $component);
//         }
//
//         $this->app->bind(ShippingMethodManagerInterface::class, function ($app) {
//             return $app->make(ShippingManager::class);
//         });
//
//         $permissions->addPermission(function ($permission) {
//             $permission->name = 'Manage Shipping';
//             $permission->handle = 'shipping:manage';
//             $permission->description = 'Allow staff ';
//             // return new Permission(
//            //     __('adminhub::auth.permissions.settings.attributes.name'),
//            //     'settings:manage-attributes',
//            //     __('adminhub::auth.permissions.settings.attributes.description')
//            // );
//         });
//
//         ShippingMenu::make();
    }
}
