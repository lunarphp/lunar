<?php

namespace Lunar\Panel\Sections\Settings;

use Closure;
use Illuminate\Support\Facades\Route;
use Lunar\Panel\Http\Controllers\Settings\LanguageCreateController;
use Lunar\Panel\Http\Controllers\Settings\LanguageEditController;
use Lunar\Panel\Http\Controllers\Settings\LanguageIndexController;
use Lunar\Panel\Navigation\NavigationItem;
use Lunar\Panel\Navigation\NavigationRegistry;
use Lunar\Panel\Sections\Section;
use Lunar\Panel\Sections\Settings\Tables\LanguagesTableExtension;

class LanguagesSection extends Section
{
    /**
     * Manifest permission handle gating both the routes (via can: middleware)
     * and the settings navigation item. Same handle as the Filament admin's
     * LanguageResource.
     */
    private const LANGUAGES_PERMISSION = 'settings:core';

    public function key(): string
    {
        return 'languages';
    }

    /** @return array<string, class-string> */
    public function tableExtensions(): array
    {
        return [
            'languages.index' => LanguagesTableExtension::class,
        ];
    }

    public function settingsNavigation(NavigationRegistry $registry): void
    {
        $registry->group('store', __('panel::nav.store'), priority: 20);
        $registry->addItem('store', new NavigationItem(
            key: 'languages',
            label: __('panel::nav.languages'),
            route: 'panel.settings.languages.index',
            permission: self::LANGUAGES_PERMISSION,
            priority: 50,
        ));
    }

    public function routes(): ?Closure
    {
        return function (): void {
            Route::prefix('settings/languages')
                ->name('panel.settings.languages.')
                ->middleware('can:'.self::LANGUAGES_PERMISSION)
                ->group(function (): void {
                    Route::get('/', [LanguageIndexController::class, 'index'])->name('index');
                    Route::post('/', [LanguageCreateController::class, 'store'])->name('store');
                    Route::get('/{language}/edit', [LanguageEditController::class, 'edit'])->name('edit');
                    Route::put('/{language}', [LanguageEditController::class, 'update'])->name('update');
                    Route::delete('/{language}', [LanguageEditController::class, 'destroy'])->name('destroy');
                });
        };
    }
}
