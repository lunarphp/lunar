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
        (new static)
            ->makeTopLevel();
    }

    /**
     * Create our top level menu.
     *
     * @return void
     */
    protected function makeTopLevel()
    {
        $slot = Menu::slot('settings');

        $slot->addItem(function ($item) {
            $item->name('Staff')
                ->handle('hub.staff')
                ->route('hub.staff.index')
                ->gate('settings:manage-staff')
                ->icon('identification');
        });

        $slot->addItem(function ($item) {
            $item->name('Channels')
                ->handle('hub.channels')
                ->route('hub.channels.index')
                ->gate('settings:core')
                ->icon('server');
        });

        $slot->addItem(function ($item) {
            $item->name('Languages')
                ->handle('hub.languages')
                ->route('hub.languages.index')
                ->gate('settings:core')
                ->icon('translate');
        });

        $slot->addItem(function ($item) {
            $item->name('Currencies')
                ->handle('hub.currencies')
                ->route('hub.currencies.index')
                ->gate('settings:core')
                ->icon('currency-pound');
        });

        $slot->addItem(function ($item) {
            $item->name('Attributes')
                ->handle('hub.attributes')
                ->route('hub.attributes.index')
                ->gate('settings:manage-attributes')
                ->icon('beaker');
        });

        $slot->addItem(function ($item) {
            $item->name('Tags')
                ->handle('hub.tags')
                ->route('hub.tags.index')
                ->gate('settings:core')
                ->icon('tag');
        });

        $slot->addItem(function ($item) {
            $item->name('Addons')
                ->handle('hub.addons')
                ->route('hub.addons.index')
                ->gate('settings:core')
                ->icon('puzzle');
        });

        $slot->addItem(function ($item) {
            $item->name('Activity Log')
                ->handle('hub.activity-log')
                ->route('hub.activity-log.index')
                ->gate('settings')
                ->icon('clipboard-list');
        });

        return $this;
    }
}
