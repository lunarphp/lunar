<?php

namespace Lunar\Panel\Sections\Settings;

use Closure;
use Illuminate\Support\Facades\Route;
use Lunar\Panel\Http\Controllers\Settings\AttributeCreateController;
use Lunar\Panel\Http\Controllers\Settings\AttributeEditController;
use Lunar\Panel\Http\Controllers\Settings\AttributeIndexController;
use Lunar\Panel\Navigation\NavigationItem;
use Lunar\Panel\Navigation\NavigationRegistry;
use Lunar\Panel\Sections\Section;
use Lunar\Panel\Sections\Settings\Tables\AttributesTableExtension;

class AttributesSection extends Section
{
    /**
     * Manifest permission handle gating both the routes (via can: middleware)
     * and the settings navigation item. Same handle as the Filament
     * admin's AttributeGroupResource.
     */
    private const ATTRIBUTES_PERMISSION = 'settings:manage-attributes';

    public function key(): string
    {
        return 'attributes';
    }

    /** @return array<string, class-string> */
    public function tableExtensions(): array
    {
        return [
            'attributes.index' => AttributesTableExtension::class,
        ];
    }

    public function settingsNavigation(NavigationRegistry $registry): void
    {
        $registry->group('catalog_settings', __('panel::nav.catalog'), priority: 40);
        $registry->addItem('catalog_settings', new NavigationItem(
            key: 'attributes',
            label: __('panel::nav.attributes'),
            route: 'panel.settings.attributes.index',
            permission: self::ATTRIBUTES_PERMISSION,
            priority: 10,
        ));
    }

    public function routes(): ?Closure
    {
        return function (): void {
            Route::prefix('settings/attributes')
                ->name('panel.settings.attributes.')
                ->middleware('can:'.self::ATTRIBUTES_PERMISSION)
                ->group(function (): void {
                    Route::get('/', [AttributeIndexController::class, 'index'])->name('index');
                    Route::post('/', [AttributeCreateController::class, 'store'])->name('store');
                    Route::get('/{attribute}/edit', [AttributeEditController::class, 'edit'])->name('edit');
                    Route::put('/{attribute}', [AttributeEditController::class, 'update'])->name('update');
                    Route::delete('/{attribute}', [AttributeEditController::class, 'destroy'])->name('destroy');
                });
        };
    }
}
