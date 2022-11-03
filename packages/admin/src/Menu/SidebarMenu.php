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

        $orders = $slot->section('orders')
            ->name(__('adminhub::menu.sidebar.orders'))
            ->handle('hub.orders')
            ->route('hub.orders.index')
            ->icon('users');

        $orders->addItem(function ($item) {
            $item
                ->name(__('adminhub::menu.sidebar.customers'))
                ->handle('hub.customers')
                ->route('hub.customers.index')
                ->icon('cash');
        });

        $products = $slot->section('products')
            ->name(__('adminhub::menu.sidebar.products'))
            ->handle('hub.products')
            ->route('hub.products.index')
            ->icon('shopping-bag');

        $products->addItem(function ($item) {
            $item
                ->name(__('adminhub::menu.sidebar.product-types'))
                ->handle('hub.product-types')
                ->route('hub.product-types.index')
                ->icon('pencil');
        });

        $products->addItem(function ($item) {
            $item
                ->name(__('adminhub::menu.sidebar.brands'))
                ->handle('hub.brands')
                ->route('hub.brands.index')
                ->icon('view-grid');
        });

        $slot->addItem(function ($item) {
            $item
                ->name(__('adminhub::menu.sidebar.collections'))
                ->handle('hub.collection')
                ->route('hub.collection-groups.index')
                ->icon('collection');
        }, 'products');

        return $this;
    }
}
