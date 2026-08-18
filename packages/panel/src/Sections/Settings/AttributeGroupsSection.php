<?php

namespace Lunar\Panel\Sections\Settings;

use Closure;
use Illuminate\Support\Facades\Route;
use Lunar\Panel\Http\Controllers\Settings\AttributeGroupCreateController;
use Lunar\Panel\Http\Controllers\Settings\AttributeGroupEditController;
use Lunar\Panel\Http\Controllers\Settings\AttributeGroupIndexController;
use Lunar\Panel\Navigation\NavigationItem;
use Lunar\Panel\Navigation\NavigationRegistry;
use Lunar\Panel\Sections\Section;
use Lunar\Panel\Sections\Settings\Tables\AttributeGroupsTableExtension;

class AttributeGroupsSection extends Section
{
    /**
     * Manifest permission handle gating both the routes (via can: middleware)
     * and the settings navigation item. Same handle as the Filament
     * admin's AttributeGroupResource.
     */
    private const ATTRIBUTE_GROUPS_PERMISSION = 'settings:manage-attributes';

    public function key(): string
    {
        return 'attribute-groups';
    }

    /** @return array<string, class-string> */
    public function tableExtensions(): array
    {
        return [
            'attribute-groups.index' => AttributeGroupsTableExtension::class,
        ];
    }

    public function settingsNavigation(NavigationRegistry $registry): void
    {
        $registry->group('catalog_settings', __('panel::nav.catalog'), priority: 40);
        $registry->addItem('catalog_settings', new NavigationItem(
            key: 'attribute-groups',
            label: __('panel::nav.attribute_groups'),
            route: 'panel.settings.attribute-groups.index',
            permission: self::ATTRIBUTE_GROUPS_PERMISSION,
            priority: 20,
        ));
    }

    public function routes(): ?Closure
    {
        return function (): void {
            Route::prefix('settings/attribute-groups')
                ->name('panel.settings.attribute-groups.')
                ->middleware('can:'.self::ATTRIBUTE_GROUPS_PERMISSION)
                ->group(function (): void {
                    Route::get('/', [AttributeGroupIndexController::class, 'index'])->name('index');
                    Route::post('/', [AttributeGroupCreateController::class, 'store'])->name('store');
                    Route::get('/{attributeGroup}/edit', [AttributeGroupEditController::class, 'edit'])->name('edit');
                    Route::put('/{attributeGroup}', [AttributeGroupEditController::class, 'update'])->name('update');
                    Route::delete('/{attributeGroup}', [AttributeGroupEditController::class, 'destroy'])->name('destroy');
                });
        };
    }
}
