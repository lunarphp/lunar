<?php

namespace Lunar\Panel\Sections\Settings;

use Closure;
use Illuminate\Support\Facades\Route;
use Lunar\Panel\Http\Controllers\Settings\ActivityLogController;
use Lunar\Panel\Navigation\NavigationItem;
use Lunar\Panel\Navigation\NavigationRegistry;
use Lunar\Panel\Sections\Section;

class ActivityLogSection extends Section
{
    /**
     * Manifest permission handle gating both the routes (via can: middleware)
     * and the settings navigation item.
     */
    private const ACTIVITY_LOG_PERMISSION = 'settings:core';

    public function key(): string
    {
        return 'activity-log';
    }

    public function settingsNavigation(NavigationRegistry $registry): void
    {
        $registry->group('logs', __('panel::nav.logs'), priority: 50);
        $registry->addItem('logs', new NavigationItem(
            key: 'activity-log',
            label: __('panel::nav.activity_log'),
            route: 'panel.settings.activity-log.index',
            permission: self::ACTIVITY_LOG_PERMISSION,
            priority: 10,
        ));
    }

    public function routes(): ?Closure
    {
        return function (): void {
            Route::prefix('settings/activity-log')
                ->name('panel.settings.activity-log.')
                ->middleware('can:'.self::ACTIVITY_LOG_PERMISSION)
                ->group(function (): void {
                    Route::get('/', [ActivityLogController::class, 'index'])->name('index');
                });
        };
    }
}
