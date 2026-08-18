<?php

namespace Lunar\Panel\Sections\Settings;

use Closure;
use Illuminate\Support\Facades\Route;
use Lunar\Panel\Http\Controllers\Settings\StaffCreateController;
use Lunar\Panel\Http\Controllers\Settings\StaffEditController;
use Lunar\Panel\Http\Controllers\Settings\StaffIndexController;
use Lunar\Panel\Navigation\NavigationItem;
use Lunar\Panel\Navigation\NavigationRegistry;
use Lunar\Panel\Sections\Section;
use Lunar\Panel\Sections\Settings\Tables\StaffTableExtension;

class StaffSection extends Section
{
    /**
     * Manifest permission handle gating both the routes (via can: middleware)
     * and the settings navigation item. Same handle as the Filament admin's
     * StaffResource.
     */
    private const STAFF_PERMISSION = 'settings:manage-staff';

    public function key(): string
    {
        return 'staff';
    }

    /** @return array<string, class-string> */
    public function tableExtensions(): array
    {
        return [
            'staff.index' => StaffTableExtension::class,
        ];
    }

    public function settingsNavigation(NavigationRegistry $registry): void
    {
        $registry->group('team', __('panel::nav.team'), priority: 10);
        $registry->addItem('team', new NavigationItem(
            key: 'staff',
            label: __('panel::nav.staff'),
            route: 'panel.settings.staff.index',
            permission: self::STAFF_PERMISSION,
            priority: 10,
        ));
    }

    public function routes(): ?Closure
    {
        return function (): void {
            Route::prefix('settings/staff')
                ->name('panel.settings.staff.')
                ->middleware('can:'.self::STAFF_PERMISSION)
                ->group(function (): void {
                    Route::get('/', [StaffIndexController::class, 'index'])->name('index');
                    Route::post('/', [StaffCreateController::class, 'store'])->name('store');
                    Route::get('/{staff}/edit', [StaffEditController::class, 'edit'])->name('edit');
                    Route::put('/{staff}', [StaffEditController::class, 'update'])->name('update');
                    Route::delete('/{staff}', [StaffEditController::class, 'destroy'])->name('destroy');
                });
        };
    }
}
