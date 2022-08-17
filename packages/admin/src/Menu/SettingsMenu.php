<?php

namespace GetCandy\Hub\Menu;

use GetCandy\Hub\Facades\Menu;

class SettingsMenu
{
    /**
     * Make our menu.
     *
     * @return void
     */
    public static function make()
    {
        (new static())
            ->makeTopLevel();
    }

    /**
     * Create our top level menu.
     *
     * @return static
     */
    protected function makeTopLevel()
    {
        $slot = Menu::slot('settings');

        $storeSection = $slot->section('store')->name(
            'Store'
        );

        $adminSection = $slot->section('admin')->name(
            'Admin'
        );

        $storeSection->addItem(function ($item) {
            $item->name('Attributes')
                ->handle('hub.attributes')
                ->route('hub.attributes.index')
                ->gate('settings:manage-attributes')
                ->icon('beaker');
        });

        $storeSection->addItem(function ($item) {
            $item->name('Channels')
                ->handle('hub.channels')
                ->route('hub.channels.index')
                ->gate('settings:core')
                ->icon('server');
        });

        $storeSection->addItem(function ($item) {
            $item->name('Currencies')
                ->handle('hub.currencies')
                ->route('hub.currencies.index')
                ->gate('settings:core')
                ->icon('currency-pound');
        });

        $storeSection->addItem(function ($item) {
            $item->name('Taxes')
                ->handle('hub.taxes')
                ->route('hub.taxes.index')
                ->gate('settings:core')
                ->icon('receipt-tax');
        });

        $storeSection->addItem(function ($item) {
            $item->name('Languages')
                ->handle('hub.languages')
                ->route('hub.languages.index')
                ->gate('settings:core')
                ->icon('translate');
        });

        $storeSection->addItem(function ($item) {
            $item->name('Tags')
                ->handle('hub.tags')
                ->route('hub.tags.index')
                ->gate('settings:core')
                ->icon('tag');
        });

        $adminSection->addItem(function ($item) {
            $item->name('Activity Log')
                ->handle('hub.activity-log')
                ->route('hub.activity-log.index')
                ->gate('settings')
                ->icon('clipboard-list');
        });

        $adminSection->addItem(function ($item) {
            $item->name('Addons')
                ->handle('hub.addons')
                ->route('hub.addons.index')
                ->gate('settings:core')
                ->icon('puzzle');
        });

        $adminSection->addItem(function ($item) {
            $item->name('Staff')
                ->handle('hub.staff')
                ->route('hub.staff.index')
                ->gate('settings:manage-staff')
                ->icon('identification');
        });

        return $this;
    }
}
