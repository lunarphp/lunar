<?php

namespace Lunar\Panel\Sections\Settings;

use Closure;
use Illuminate\Support\Facades\Route;
use Lunar\Panel\Http\Controllers\Settings\RoleController;
use Lunar\Panel\Navigation\NavigationItem;
use Lunar\Panel\Navigation\NavigationRegistry;
use Lunar\Panel\Sections\Section;
use Lunar\Panel\Sections\Settings\Tables\RolesTableExtension;

class RolesSection extends Section
{
    /**
     * Manifest permission handle gating both the routes (via can: middleware)
     * and the settings navigation item. Roles govern staff access, so they
     * share the staff management permission.
     */
    private const ROLES_PERMISSION = 'settings:manage-staff';

    public function key(): string
    {
        return 'roles';
    }

    /** @return array<string, class-string> */
    public function tableExtensions(): array
    {
        return [
            'roles.index' => RolesTableExtension::class,
        ];
    }

    public function settingsNavigation(NavigationRegistry $registry): void
    {
        $registry->group('team', __('panel::nav.team'), priority: 10);
        $registry->addItem('team', new NavigationItem(
            key: 'roles',
            label: __('panel::nav.roles'),
            route: 'panel.settings.roles.index',
            permission: self::ROLES_PERMISSION,
            priority: 20,
        ));
    }

    public function routes(): ?Closure
    {
        return function (): void {
            Route::prefix('settings/roles')
                ->name('panel.settings.roles.')
                ->middleware('can:'.self::ROLES_PERMISSION)
                ->group(function (): void {
                    Route::get('/', [RoleController::class, 'index'])->name('index');
                    Route::post('/', [RoleController::class, 'store'])->name('store');
                    Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit');
                    Route::put('/{role}', [RoleController::class, 'update'])->name('update');
                    Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
                });
        };
    }
}
