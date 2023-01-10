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

        $catalogueGroup = $slot
            ->group('hub.catalogue')
            ->name(__('adminhub::menu.sidebar.catalogue'));

        $salesGroup = $slot
            ->group('hub.sales')
            ->name(__('adminhub::menu.sidebar.sales'));

        $productGroup = $catalogueGroup
            ->section('hub.products')
            ->name(__('adminhub::menu.sidebar.products'))
            ->handle('hub.products')
            ->route('hub.products.index')
            ->icon('shopping-bag');

        $catalogueGroup
            ->section('hub.collections')
            ->name(__('adminhub::menu.sidebar.collections'))
            ->handle('hub.collection-groups')
            ->route('hub.collection-groups.index')
            ->icon('collection');

        $productGroup->addItem(function ($menuItem) {
            $menuItem
                ->name(__('adminhub::menu.sidebar.product-types'))
                ->handle('hub.product-types')
                ->route('hub.product-types.index');
        });

        $productGroup->addItem(function ($menuItem) {
            $menuItem
                ->name(__('adminhub::menu.sidebar.brands'))
                ->handle('hub.brands')
                ->route('hub.brands.index');
        });

        $salesGroup->addItem(function ($menuItem) {
            $menuItem
                ->name(__('adminhub::menu.sidebar.orders'))
                ->handle('hub.orders')
                ->route('hub.orders.index')
                ->icon('cash');
        });

        $salesGroup->addItem(function ($menuItem) {
            $menuItem
                ->name(__('adminhub::menu.sidebar.customers'))
                ->handle('hub.customers')
                ->route('hub.customers.index')
                ->icon('users');
        });

        $salesGroup->addItem(function ($menuItem) {
            $menuItem
                ->name(__('adminhub::menu.sidebar.discounts'))
                ->handle('hub.discounts')
                ->route('hub.discounts.index')
                ->icon('ticket');
        });

        $salesGroup->addItem(function ($menuItem) {
            $menuItem
                ->name(__('adminhub::menu.sidebar.discounts'))
                ->handle('hub.discounts')
                ->route('hub.discounts.index')
                ->icon('ticket');
        });
        $salesGroup->addItem(function ($menuItem) {
            $menuItem
                ->name(__('adminhub::menu.sidebar.discounts'))
                ->handle('hub.discounts')
                ->route('hub.discounts.index')
                ->icon('ticket');
        });
        $salesGroup->addItem(function ($menuItem) {
            $menuItem
                ->name(__('adminhub::menu.sidebar.discounts'))
                ->handle('hub.discounts')
                ->route('hub.discounts.index')
                ->icon('ticket');
        });
        $salesGroup->addItem(function ($menuItem) {
            $menuItem
                ->name(__('adminhub::menu.sidebar.discounts'))
                ->handle('hub.discounts')
                ->route('hub.discounts.index')
                ->icon('ticket');
        });
        $salesGroup->addItem(function ($menuItem) {
            $menuItem
                ->name(__('adminhub::menu.sidebar.discounts'))
                ->handle('hub.discounts')
                ->route('hub.discounts.index')
                ->icon('ticket');
        });
        $salesGroup->addItem(function ($menuItem) {
            $menuItem
                ->name(__('adminhub::menu.sidebar.discounts'))
                ->handle('hub.discounts')
                ->route('hub.discounts.index')
                ->icon('ticket');
        });
        $salesGroup->addItem(function ($menuItem) {
            $menuItem
                ->name(__('adminhub::menu.sidebar.discounts'))
                ->handle('hub.discounts')
                ->route('hub.discounts.index')
                ->icon('ticket');
        });

        $salesGroup->addItem(function ($menuItem) {
            $menuItem
                ->name(__('adminhub::menu.sidebar.discounts'))
                ->handle('hub.discounts')
                ->route('hub.discounts.index')
                ->icon('ticket');
        });

        $salesGroup->addItem(function ($menuItem) {
            $menuItem
                ->name(__('adminhub::menu.sidebar.discounts'))
                ->handle('hub.discounts')
                ->route('hub.discounts.index')
                ->icon('ticket');
        });



        return $this;
    }
}
