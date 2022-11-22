<?php

namespace Lunar\Hub\Menu;

use Lunar\Hub\Facades\Menu;

class SidebarMenu
{
    /**
     * Make menu.
     *
     * @return void
     */
    public static function make()
    {
        (new static())
            ->makeTopLevel()
            ->addSections();
    }

    /**
     * Make top level navigation.
     *
     * @return static
     */
    protected function makeTopLevel()
    {
        $slot = Menu::slot('sidebar');

        $slot->addItem(function ($item) {
            $item
                ->name(__('adminhub::menu.sidebar.index'))
                ->handle('hub.index')
                ->route('hub.index')
                ->icon('chart-square-bar');
        });

        return $this;
    }

    /**
     * Add our menu sections.
     *
     * @return static
     */
    protected function addSections()
    {
        $slot = Menu::slot('sidebar');

        $catalogGroup = $slot
            ->group('hub.catalog')
            ->name('Catalogue');

        $salesGroup = $slot
            ->group('hub.sales')
            ->name('Sales');

        $productGroup = $catalogGroup
            ->section('hub.products')
            ->name('Products')
            ->handle('hub.products')
            ->route('hub.products.index')
            ->icon('shopping-bag');

        $catalogGroup
            ->section('hub.collections')
            ->name('Collections')
            ->handle('hub.collection-groups')
            ->route('hub.collection-groups.index')
            ->icon('collection');

        $productGroup->addItem(function ($menuItem) {
            $menuItem
                ->name('Products Types')
                ->handle('hub.product-types')
                ->route('hub.product-types.index');
        });

        $productGroup->addItem(function ($menuItem) {
            $menuItem
                ->name('Brands')
                ->handle('hub.brands')
                ->route('hub.brands.index');
        });

        $salesGroup->addItem(function ($menuItem) {
            $menuItem
                ->name('Orders')
                ->handle('hub.orders')
                ->route('hub.orders.index')
                ->icon('cash');
        });

        $salesGroup->addItem(function ($menuItem) {
            $menuItem
                ->name('Customers')
                ->handle('hub.customers')
                ->route('hub.customers.index')
                ->icon('users');
        });

        $salesGroup->addItem(function ($menuItem) {
            $menuItem
                ->name('Discounts')
                ->handle('hub.discounts')
                ->route('hub.index')
                ->icon('ticket');
        });

        return $this;
    }
}
