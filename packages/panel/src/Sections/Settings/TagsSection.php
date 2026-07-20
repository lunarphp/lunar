<?php

namespace Lunar\Panel\Sections\Settings;

use Closure;
use Illuminate\Support\Facades\Route;
use Lunar\Panel\Http\Controllers\Settings\TagController;
use Lunar\Panel\Navigation\NavigationItem;
use Lunar\Panel\Navigation\NavigationRegistry;
use Lunar\Panel\Sections\Section;
use Lunar\Panel\Sections\Settings\Tables\TagsTableExtension;

class TagsSection extends Section
{
    /**
     * Manifest permission handle gating both the routes (via can: middleware)
     * and the settings navigation item.
     */
    private const TAGS_PERMISSION = 'settings:core';

    public function key(): string
    {
        return 'tags';
    }

    /** @return array<string, class-string> */
    public function tableExtensions(): array
    {
        return [
            'tags.index' => TagsTableExtension::class,
        ];
    }

    public function settingsNavigation(NavigationRegistry $registry): void
    {
        $registry->group('store', __('panel::nav.store'), priority: 20);
        $registry->addItem('store', new NavigationItem(
            key: 'tags',
            label: __('panel::nav.tags'),
            route: 'panel.settings.tags.index',
            permission: self::TAGS_PERMISSION,
            priority: 80,
        ));
    }

    public function routes(): ?Closure
    {
        return function (): void {
            Route::prefix('settings/tags')
                ->name('panel.settings.tags.')
                ->middleware('can:'.self::TAGS_PERMISSION)
                ->group(function (): void {
                    Route::get('/', [TagController::class, 'index'])->name('index');
                    Route::post('/', [TagController::class, 'store'])->name('store');
                    Route::put('/{tag}', [TagController::class, 'update'])->name('update');
                    Route::delete('/{tag}', [TagController::class, 'destroy'])->name('destroy');
                });
        };
    }
}
