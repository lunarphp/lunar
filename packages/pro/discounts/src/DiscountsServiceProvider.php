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
use GetCandy\Discounts\Http\Livewire\Components\CouponEdit;
use GetCandy\Discounts\Http\Livewire\DiscountShow;
use GetCandy\Discounts\Http\Livewire\DiscountsIndex;
use GetCandy\Discounts\Interfaces\DiscountRewardManagerInterface;
use GetCandy\Discounts\Interfaces\DiscountRuleManagerInterface;
use GetCandy\Discounts\Interfaces\DiscountsInterface;
use GetCandy\Discounts\Managers\DiscountManager;
use GetCandy\Discounts\Managers\DiscountRewardManager;
use GetCandy\Discounts\Managers\DiscountRuleManager;
use GetCandy\Discounts\Models\Discount;
use GetCandy\Discounts\Modifiers\DiscountCartModifier;
use GetCandy\Facades\AttributeManifest;
use GetCandy\Hub\Facades\Menu;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class DiscountsServiceProvider extends ServiceProvider
{
    public function boot(
        CartLineModifiers $cartModifiers,
    ) {
        $this->app->singleton(DiscountsInterface::class, function ($app) {
            return $app->make(DiscountManager::class);
        });

        $this->app->singleton(DiscountRuleManagerInterface::class, function ($app) {
            return $app->make(DiscountRuleManager::class);
        });

        $this->app->singleton(DiscountRewardManagerInterface::class, function ($app) {
            return $app->make(DiscountRewardManager::class);
        });

        $cartModifiers->add(
            DiscountCartModifier::class
        );

        $this->loadRoutesFrom(__DIR__ . '/../routes/hub.php');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'discounts');

        $slot = Menu::slot('sidebar');

        $slot->addItem(function ($item) {
            $item->name(
                __('discounts::index.menu_item')
            )->handle('hub.discounts')
            ->route('hub.discounts.index')
            ->icon('ticket');

            $item->name(
                __('discounts::index.menu_item')
            )->handle('hub.discounts')
            ->route('hub.discounts.index')
            ->icon('ticket');
        });

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'discounts');

        $components = [
            DiscountsIndex::class,
            DiscountShow::class,
            CouponEdit::class,
        ];

        foreach ($components as $component) {
            Livewire::component((new $component())->getName(), $component);
        }

        AttributeManifest::addType(Discount::class);
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
