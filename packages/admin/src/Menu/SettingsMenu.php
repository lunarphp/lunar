<?php

namespace GetCandy\Hub\Menu;

use GetCandy\Hub\Facades\Menu;

class SettingsMenu
{
    protected MenuSlot $slot;

    /**
     * Make our menu.
     *
     * @return void
     */
    public static function make(): void
    {
        (new static())
            ->makeTopLevel();
    }

    /**
     * Create our top level menu.
     *
     * @return static
     */
    protected function makeTopLevel(): static
    {
        $this->slot = Menu::slot('settings');

        $this->makeStoreSection();
        $this->makeProductSection();
        $this->makeAdminSection();

        return $this;
    }

    /**
     * Create the store sections.
     *
     * @return void
     */
    protected function makeStoreSection(): void
    {
        $storeSection = $this->slot->section('store')->name('Store');

        $storeSection->addItem(function (MenuLink $item) {
            $item->name('Attributes')
                 ->handle('hub.attributes')
                 ->route('hub.attributes.index')
                 ->gate('settings:manage-attributes')
                 ->icon('beaker');
        });

        $storeSection->addItem(function (MenuLink $item) {
            $item->name('Channels')
                 ->handle('hub.channels')
                 ->route('hub.channels.index')
                 ->gate('settings:core')
                 ->icon('server');
        });

        $storeSection->addItem(function (MenuLink $item) {
            $item->name('Currencies')
                 ->handle('hub.currencies')
                 ->route('hub.currencies.index')
                 ->gate('settings:core')
                 ->icon('currency-pound');
        });

        $storeSection->addItem(function (MenuLink $item) {
            $item->name('Languages')
                 ->handle('hub.languages')
                 ->route('hub.languages.index')
                 ->gate('settings:core')
                 ->icon('translate');
        });

        $storeSection->addItem(function (MenuLink $item) {
            $item->name('Tags')
                 ->handle('hub.tags')
                 ->route('hub.tags.index')
                 ->gate('settings:core')
                 ->icon('tag');
        });
    }

    /**
     * Create the product sections.
     *
     * @return void
     */
    protected function makeProductSection(): void
    {
        $productSection = $this->slot->section('product')->name('Product');

        $productSection->addItem(function (MenuLink $item) {
            $item->name('Features')
                 ->handle('hub.product.features')
                 ->route('hub.product.features.index')
                 ->gate('settings:core')
                 ->icon('clipboard-list');
        });

        $productSection->addItem(function (MenuLink $item) {
            $item->name('Options')
                 ->handle('hub.product.options')
                 ->route('hub.product.options.index')
                 ->gate('settings:core')
                 ->icon('clipboard-list');
        });
    }

    /**
     * Create the admin sections.
     *
     * @return void
     */
    protected function makeAdminSection(): void
    {
        $adminSection = $this->slot->section('admin')->name('Admin');

        $adminSection->addItem(function (MenuLink $item) {
            $item->name('Activity Log')
                 ->handle('hub.activity-log')
                 ->route('hub.activity-log.index')
                 ->gate('settings')
                 ->icon('clipboard-list');
        });

        $adminSection->addItem(function (MenuLink $item) {
            $item->name('Addons')
                 ->handle('hub.addons')
                 ->route('hub.addons.index')
                 ->gate('settings:core')
                 ->icon('puzzle');
        });

        $adminSection->addItem(function (MenuLink $item) {
            $item->name('Staff')
                 ->handle('hub.staff')
                 ->route('hub.staff.index')
                 ->gate('settings:manage-staff')
                 ->icon('identification');
        });
    }
}
