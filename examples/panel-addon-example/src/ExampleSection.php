<?php

namespace LunarPanelExample;

use Closure;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Lunar\Panel\Navigation\NavigationItem;
use Lunar\Panel\Navigation\NavigationRegistry;
use Lunar\Panel\Sections\Section;
use Lunar\Panel\Slots\Slot;
use Lunar\Panel\Slots\SlotRegistry;
use LunarPanelExample\Tables\ExampleTableExtension;

class ExampleSection extends Section
{
    public function key(): string
    {
        return 'example-addon';
    }

    public function navigation(NavigationRegistry $registry): void
    {
        $registry->group('example-addon-group', 'Example Add-on');
        $registry->addItem('example-addon-group', new NavigationItem(
            key: 'example-addon',
            label: 'Example Add-on',
            route: 'panel.example-addon.index',
        ));
    }

    public function routes(): ?Closure
    {
        return function (): void {
            Route::get('example-addon', fn () => Inertia::render('example-addon::Widgets/Index', [
                'message' => 'Hello from the example add-on! This page was registered at runtime via window.LunarPanel.registerPages(), not compiled into the panel.',
            ]))->name('panel.example-addon.index');
        };
    }

    public function slots(SlotRegistry $registry): void
    {
        // Demonstrates the slot mechanism on the Customer edit page. The zone prefix must
        // match the page's route name with the "panel." prefix stripped — our route is
        // named panel.customers.edit (there's no separate "show" route), so the zone is
        // "customers.edit", not the spec's illustrative "customers.show".
        $registry->add(new Slot(
            zone: 'customers.edit:main:after',
            component: 'example-addon::InfoBanner',
            props: ['message' => 'This banner was injected by the example add-on via a slot.'],
        ));
    }

    public function tableExtensions(): array
    {
        return ['customers.index' => ExampleTableExtension::class];
    }
}
