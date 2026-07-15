<?php

namespace LunarPanelExample;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Lunar\Core\Models\Customer;
use Lunar\Panel\Navigation\NavigationItem;
use Lunar\Panel\Navigation\NavigationRegistry;
use Lunar\Panel\Sections\Section;
use Lunar\Panel\Slots\Slot;
use Lunar\Panel\Slots\SlotRegistry;
use LunarPanelExample\Actions\AuditPageAction;
use LunarPanelExample\Actions\ImportPageAction;
use LunarPanelExample\Tables\ExampleTableExtension;

class ExampleSection extends Section
{
    /**
     * Manifest permission handle gating the routes and the navigation item
     * together, mirroring how first-party sections keep what a user sees and
     * what they can reach in lockstep. This add-on extends the Customers area,
     * so it reuses the core sales:manage-customers handle; an add-on with its
     * own domain would seed and use its own handle instead.
     */
    private const PERMISSION = 'sales:manage-customers';

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
            permission: self::PERMISSION,
        ));
    }

    public function routes(): ?Closure
    {
        return function (): void {
            Route::middleware('can:'.self::PERMISSION)->group(function (): void {
                Route::get('example-addon', fn () => Inertia::render('example-addon::Widgets/Index', [
                    'message' => 'Hello from the example add-on! This page was registered at runtime via window.LunarPanel.registerPages(), not compiled into the panel.',
                ]))->name('panel.example-addon.index');

                // Demo endpoints the injected actions target. Each just flashes back
                // so the add-on stays side-effect free.
                Route::get('example-addon/ping/{customer}', fn (Customer $customer) => back()
                    ->with('success', "Pinged {$customer->full_name} (example row action)."))
                    ->name('panel.example-addon.ping');

                Route::post('example-addon/bulk-ping', fn (Request $request) => back()
                    ->with('success', count((array) $request->input('ids', [])).' customers pinged (example bulk action).'))
                    ->name('panel.example-addon.bulk-ping');

                Route::get('example-addon/import', fn () => back()
                    ->with('success', 'Import started (example listing action).'))
                    ->name('panel.example-addon.import');

                Route::get('example-addon/customers/{customer}/audit', fn (Customer $customer) => back()
                    ->with('success', "Audit log opened for {$customer->full_name} (example record action)."))
                    ->name('panel.example-addon.audit');
            });
        };
    }

    /** @return array<string, array<int, class-string>> */
    public function pageActions(): array
    {
        return [
            'customers.index' => [ImportPageAction::class],
            'customers.edit' => [AuditPageAction::class],
        ];
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
