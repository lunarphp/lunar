<?php

namespace Lunar\Hub\Menu;

use Lunar\Hub\Facades\Menu;

final class SettingsMenu
{
    protected MenuSlot $slot;

    /**
     * Make our menu.
     */
    public static function make(): void
    {
        (new self())
            ->makeTopLevel();
    }

    /**
     * Create our top level menu.
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
     */
    protected function makeStoreSection(): void
    {
        $storeSection = $this->slot->section('store')->name(__('adminhub::global.store'));

        $storeSection->addItem(function (MenuLink $item) {
            $item->name(__('adminhub::settings.attributes.index.title'))
                ->handle('hub.attributes')
                ->route('hub.attributes.index')
                ->gate('settings:manage-attributes')
                ->icon('beaker');
        });

        $storeSection->addItem(function (MenuLink $item) {
            $item->name(__('adminhub::settings.channels.index.title'))
                ->handle('hub.channels')
                ->route('hub.channels.index')
                ->gate('settings:core')
                ->icon('server');
        });

        $storeSection->addItem(function (MenuLink $item) {
            $item->name(__('adminhub::settings.currencies.index.title'))
                ->handle('hub.currencies')
                ->route('hub.currencies.index')
                ->gate('settings:core')
                ->icon('currency-pound');
        });

        $storeSection->addItem(function (MenuLink $item) {
            $item->name(__('adminhub::settings.customer-groups.index.title'))
                ->handle('hub.customer-groups')
                ->route('hub.customer-groups.index')
                ->gate('settings:manage-staff')
                ->icon('user');
        });

        $storeSection->addItem(function ($item) {
            $item->name(__('adminhub::settings.languages.index.title'))
                ->handle('hub.languages')
                ->route('hub.languages.index')
                ->gate('settings:core')
                ->icon('translate');
        });

        $storeSection->addItem(function (MenuLink $item) {
            $item->name(__('adminhub::settings.tags.index.title'))
                ->handle('hub.tags')
                ->route('hub.tags.index')
                ->gate('settings:core')
                ->icon('tag');
        });

        $storeSection->addItem(function (MenuLink $item) {
            $item->name(__('adminhub::settings.taxes.tax-zones.index.title'))
                ->handle('hub.taxes')
                ->route('hub.taxes.index')
                ->gate('settings:core')
                ->icon('receipt-tax');
        });
    }

    /**
     * Create the product sections.
     */
    protected function makeProductSection(): void
    {
        $productSection = $this->slot->section('product')->name(__('adminhub::global.product'));

        $productSection->addItem(function (MenuLink $item) {
            $item->name(__('adminhub::settings.product.options.index.title'))
                ->handle('hub.product.options')
                ->route('hub.product.options.index')
                ->gate('settings:core')
                ->icon('clipboard-list');
        });
    }

    /**
     * Create the admin sections.
     */
    protected function makeAdminSection(): void
    {
        $adminSection = $this->slot->section('admin')->name(__('adminhub::global.admin'));

        $adminSection->addItem(function (MenuLink $item) {
            $item->name(__('adminhub::settings.activity_log.index.title'))
                ->handle('hub.activity-log')
                ->route('hub.activity-log.index')
                ->gate('settings:core')
                ->icon('clipboard-list');
        });

        $adminSection->addItem(function (MenuLink $item) {
            $item->name(__('adminhub::settings.addons.index.title'))
                ->handle('hub.addons')
                ->route('hub.addons.index')
                ->gate('settings:core')
                ->icon('puzzle');
        });

        $adminSection->addItem(function (MenuLink $item) {
            $item->name(__('adminhub::settings.staff.index.title'))
                ->handle('hub.staff')
                ->route('hub.staff.index')
                ->gate('settings:manage-staff')
                ->icon('identification');
        });
    }
}
