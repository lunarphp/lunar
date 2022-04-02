<?php

namespace GetCandy\Hub\Menu;

use GetCandy\Hub\Facades\Menu;

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
            $item->name(
                __('adminhub::menu.sidebar.index')
            )->handle('hub.index')
            ->route('hub.index')
            ->icon('chart-square-bar')
            ->position(0);
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

        // $catalogueManager = $slot->section('catalogue-manager')->name(
        //     __('adminhub::menu.sidebar.catalogue-manager')
        // );

        $slot->addItem(function ($item) {
            $item->name(
                __('adminhub::menu.sidebar.products')
            )->handle('hub.products')
            ->route('hub.products.index')
            ->icon('shopping-bag')
            ->position(1);
        });

        $slot->addItem(function ($item) {
            $item->name(
                __('adminhub::menu.sidebar.product-types')
            )->handle('hub.product-type')
            ->route('hub.product-types.index')
            ->icon('pencil')
            ->position(2);
        });

        $slot->addItem(function ($item) {
            $item->name(
                __('adminhub::menu.sidebar.collections')
            )->handle('hub.collection')
            ->route('hub.collection-groups.index')
            ->icon('collection')
            ->position(3);
        }, 'products');

        // $orders = $slot->section('order-processing')->name(
        //     __('adminhub::menu.sidebar.order-processing')
        // );

        $slot->addItem(function ($item) {
            $item->name(
                __('adminhub::menu.sidebar.orders')
            )->handle('hub.orders')
            ->route('hub.orders.index')
            ->icon('cash')
            ->position(4);
        });

        $slot->addItem(function ($item) {
            $item->name(
                __('adminhub::menu.sidebar.customers')
            )->handle('hub.customers')
            ->route('hub.customers.index')
            ->icon('users')
            ->position(5);
        });

        return $this;
    }
}
