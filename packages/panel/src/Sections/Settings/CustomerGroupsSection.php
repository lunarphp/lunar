<?php

namespace Lunar\Panel\Sections\Settings;

use Closure;
use Illuminate\Support\Facades\Route;
use Lunar\Panel\Http\Controllers\Settings\CustomerGroupCreateController;
use Lunar\Panel\Http\Controllers\Settings\CustomerGroupEditController;
use Lunar\Panel\Http\Controllers\Settings\CustomerGroupIndexController;
use Lunar\Panel\Navigation\NavigationItem;
use Lunar\Panel\Navigation\NavigationRegistry;
use Lunar\Panel\Sections\Section;
use Lunar\Panel\Sections\Settings\Tables\CustomerGroupsTableExtension;

class CustomerGroupsSection extends Section
{
    /**
     * Manifest permission handle gating both the routes (via can: middleware)
     * and the settings navigation item. Same handle as the Filament admin's
     * CustomerGroupResource.
     */
    private const CUSTOMER_GROUPS_PERMISSION = 'settings:core';

    public function key(): string
    {
        return 'customer-groups';
    }

    /** @return array<string, class-string> */
    public function tableExtensions(): array
    {
        return [
            'customer-groups.index' => CustomerGroupsTableExtension::class,
        ];
    }

    public function settingsNavigation(NavigationRegistry $registry): void
    {
        $registry->group('store', __('panel::nav.store'), priority: 20);
        $registry->addItem('store', new NavigationItem(
            key: 'customer-groups',
            label: __('panel::nav.customer_groups'),
            route: 'panel.settings.customer-groups.index',
            permission: self::CUSTOMER_GROUPS_PERMISSION,
            priority: 30,
        ));
    }

    public function routes(): ?Closure
    {
        return function (): void {
            Route::prefix('settings/customer-groups')
                ->name('panel.settings.customer-groups.')
                ->middleware('can:'.self::CUSTOMER_GROUPS_PERMISSION)
                ->group(function (): void {
                    Route::get('/', [CustomerGroupIndexController::class, 'index'])->name('index');
                    Route::post('/', [CustomerGroupCreateController::class, 'store'])->name('store');
                    Route::get('/{customerGroup}/edit', [CustomerGroupEditController::class, 'edit'])->name('edit');
                    Route::put('/{customerGroup}', [CustomerGroupEditController::class, 'update'])->name('update');
                    Route::delete('/{customerGroup}', [CustomerGroupEditController::class, 'destroy'])->name('destroy');
                });
        };
    }
}
