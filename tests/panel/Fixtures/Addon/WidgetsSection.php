<?php

namespace Lunar\Tests\Panel\Fixtures\Addon;

use Closure;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Lunar\Panel\Navigation\NavigationItem;
use Lunar\Panel\Navigation\NavigationRegistry;
use Lunar\Panel\Sections\Section;
use Lunar\Panel\Slots\Slot;
use Lunar\Panel\Slots\SlotRegistry;
use Lunar\Tests\Panel\Fixtures\Addon\Tables\WidgetTableExtension;

class WidgetsSection extends Section
{
    public function key(): string
    {
        return 'widgets';
    }

    public function navigation(NavigationRegistry $registry): void
    {
        $registry->group('widgets-group', 'Widgets');
        $registry->addItem('widgets-group', new NavigationItem(
            key: 'widgets',
            label: 'Widgets',
            route: 'panel.widgets.index',
        ));
    }

    public function slots(SlotRegistry $registry): void
    {
        $registry->add(new Slot(
            zone: 'widgets.index:main:after',
            component: 'widgets::Banner',
            priority: 10,
        ));
    }

    public function routes(): ?Closure
    {
        return function (): void {
            Route::get('widgets', fn () => Inertia::render('Widgets/Index'))
                ->name('panel.widgets.index');
        };
    }

    public function tableExtensions(): array
    {
        return ['widgets.index' => WidgetTableExtension::class];
    }

    public function langNamespaces(): array
    {
        return ['widgets-addon'];
    }
}
