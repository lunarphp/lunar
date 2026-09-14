<?php

namespace Lunar\SearchRelevance\Panel;

use Closure;
use Illuminate\Support\Facades\Route;
use Lunar\Panel\Navigation\NavigationItem;
use Lunar\Panel\Navigation\NavigationRegistry;
use Lunar\Panel\Sections\Section;
use Lunar\Panel\Slots\Slot;
use Lunar\Panel\Slots\SlotRegistry;
use Lunar\Panel\Support\Position;
use Lunar\SearchRelevance\Panel\Http\Controllers\OverridesController;
use Lunar\SearchRelevance\Panel\Http\Controllers\SearchRelevanceController;
use Lunar\SearchRelevance\Panel\Http\Controllers\SettingsController;
use Lunar\SearchRelevance\Panel\Search\QuerySearchSource;
use Lunar\SearchRelevance\Panel\Widgets\SearchConversionWidget;

class SearchRelevanceSection extends Section
{
    public const PERMISSION = 'search:manage-relevance';

    public function key(): string
    {
        return 'search-relevance';
    }

    public function label(): string
    {
        return __('search-relevance::panel.nav_label');
    }

    public function navigation(NavigationRegistry $registry): void
    {
        // Reporting, not day-to-day operations: sits below the first-party groups.
        $registry->group('search', 'search-relevance::panel.nav_group', position: Position::last());
        $registry->addItem('search', new NavigationItem(
            key: 'search-relevance',
            label: 'search-relevance::panel.nav_label',
            icon: 'chart',
            route: 'panel.search-relevance.index',
            permission: self::PERMISSION,
        ));
    }

    public function settingsNavigation(NavigationRegistry $registry): void
    {
        $registry->group('store', 'panel::nav.store', priority: 20);
        $registry->addItem('store', new NavigationItem(
            key: 'search-relevance',
            label: 'search-relevance::panel.settings_label',
            route: 'panel.settings.search-relevance.index',
            permission: self::PERMISSION,
        ));
    }

    public function routes(): ?Closure
    {
        return function (): void {
            Route::middleware('can:'.self::PERMISSION)->group(function (): void {
                Route::get('search-relevance', [SearchRelevanceController::class, 'index'])
                    ->name('panel.search-relevance.index');

                // A normalised query may contain any character, slashes included.
                Route::get('search-relevance/queries/{query}', [SearchRelevanceController::class, 'query'])
                    ->where('query', '.*')
                    ->name('panel.search-relevance.query');

                Route::get('search-relevance/products/{product}', [SearchRelevanceController::class, 'product'])
                    ->name('panel.search-relevance.product');

                // {productId}, not {product}: the panel binds `product` to the model.
                Route::post('search-relevance/queries/{query}/exclusions/{productId}', [OverridesController::class, 'exclude'])
                    ->where(['query' => '.*', 'productId' => '[0-9]+'])
                    ->name('panel.search-relevance.exclude');
                Route::delete('search-relevance/queries/{query}/exclusions/{productId}', [OverridesController::class, 'include'])
                    ->where(['query' => '.*', 'productId' => '[0-9]+'])
                    ->name('panel.search-relevance.include');
                Route::post('search-relevance/queries/{query}/reset', [OverridesController::class, 'reset'])
                    ->where('query', '.*')
                    ->name('panel.search-relevance.reset');

                Route::prefix('settings/search-relevance')
                    ->name('panel.settings.search-relevance.')
                    ->group(function (): void {
                        Route::get('/', [SettingsController::class, 'index'])->name('index');
                    });
            });
        };
    }

    public function slots(SlotRegistry $registry): void
    {
        $registry->add(new Slot(
            zone: 'products.edit:content:after',
            component: 'search-relevance::ProductSearchPerformance',
            permission: self::PERMISSION,
        ));
    }

    public function widgets(): array
    {
        return [SearchConversionWidget::class];
    }

    public function searchSources(): array
    {
        return [QuerySearchSource::class];
    }

    public function langNamespaces(): array
    {
        return ['search-relevance'];
    }
}
