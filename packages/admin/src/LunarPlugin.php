<?php

namespace Lunar\Admin;

use Filament\Contracts\Plugin;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\Support\Facades\FilamentIcon;
use Lunar\Admin\Filament\Pages;
use Lunar\Admin\Filament\Resources;
use Lunar\Admin\Filament\Resources\OrderResource\Pages\Components\OrderItemsTable;

class LunarPlugin implements Plugin
{
    protected static $resources = [
        Resources\ActivityResource::class,
        Resources\AttributeGroupResource::class,
        Resources\BrandResource::class,
        Resources\ChannelResource::class,
        Resources\CollectionGroupResource::class,
        Resources\CurrencyResource::class,
        Resources\CustomerGroupResource::class,
        Resources\CustomerResource::class,
        Resources\LanguageResource::class,
        Resources\OrderResource::class,
        Resources\ProductOptionResource::class,
        Resources\ProductResource::class,
        Resources\ProductTypeResource::class,
        Resources\StaffResource::class,
        Resources\TagResource::class,
        Resources\TaxClassResource::class,
        Resources\TaxZoneResource::class,
    ];

    protected static $pages = [
        Pages\Dashboard::class,
    ];

    protected static $widgets = [
    ];

    public function getId(): string
    {
        return 'lunar';
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function getResources(): array
    {
        return static::$resources;
    }

    public static function getPages(): array
    {
        return static::$pages;
    }

    public static function getWidgets(): array
    {
        return static::$widgets;
    }

    protected static function switch(&$array, $originalClass, $newClass): void
    {
        $key = array_search($originalClass, $array);
        $array[$key] = $newClass;
    }

    public static function switchResource($originalClass, $newClass): void
    {
        static::switch(
            static::$resources,
            $originalClass,
            $newClass
        );
    }

    public static function switchPage($originalClass, $newClass): void
    {
        static::switch(
            static::$pages,
            $originalClass,
            $newClass
        );
    }

    public static function switchWidget($originalClass, $newClass): void
    {
        static::switch(
            static::$widgets,
            $originalClass,
            $newClass
        );
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources(static::getResources())
            ->pages(static::getPages())
            ->widgets(static::getWidgets())
            ->discoverLivewireComponents(__DIR__.'/Livewire', 'Lunar\\Admin\\Livewire')
            ->livewireComponents([
                OrderItemsTable::class,
                \Lunar\Admin\Filament\Resources\CollectionGroupResource\Widgets\CollectionTreeView::class,
            ])
            ->navigationGroups([
                'Catalog',
                'Sales',
                NavigationGroup::make()
                    ->label('Settings')
                    ->collapsed(),
            ])
            ->navigationItems([
                NavigationItem::make('Discounts')
                    ->url('#')
                    ->icon('lucide-percent-circle')
                    ->group('Sales')
                    ->sort(3),
            ]);

        FilamentIcon::register([
            // Filament
            'panels::topbar.global-search.field' => 'lucide-search',
            'actions::view-action' => 'lucide-eye',
            'actions::edit-action' => 'lucide-edit',
            'actions::delete-action' => 'lucide-trash-2',

            // Lunar
            'lunar::activity' => 'lucide-activity',
            'lunar::attributes' => 'lucide-pencil-ruler',
            'lunar::availability' => 'lucide-calendar',
            'lunar::basic-information' => 'lucide-edit',
            'lunar::brands' => 'lucide-badge-check',
            'lunar::channels' => 'lucide-store',
            'lunar::collections' => 'lucide-blocks',
            'lunar::currencies' => 'lucide-circle-dollar-sign',
            'lunar::customers' => 'lucide-users',
            'lunar::customer-groups' => 'lucide-users',
            'lunar::dashboard' => 'lucide-bar-chart-big',
            'lunar::languages' => 'lucide-languages',
            'lunar::media' => 'lucide-image',
            'lunar::orders' => 'lucide-inbox',
            'lunar::product-pricing' => 'lucide-wallet',
            'lunar::product-associations' => 'lucide-cable',
            'lunar::product-options' => 'lucide-list',
            'lunar::product-variants' => 'lucide-shapes',
            'lunar::products' => 'lucide-tag',
            'lunar::staff' => 'lucide-shield',
            'lunar::tags' => 'lucide-tags',
            'lunar::tax' => 'lucide-landmark',
            'lunar::urls' => 'lucide-globe',
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
