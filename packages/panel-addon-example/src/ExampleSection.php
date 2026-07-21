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
        // Labels are ordinary namespaced lang keys — the registry resolves
        // them through __() when the navigation tree is shared.
        $registry->group('example-addon-group', 'example-addon::example.nav_group');
        $registry->addItem('example-addon-group', new NavigationItem(
            key: 'example-addon',
            label: 'example-addon::example.nav_label',
            // Icon names come from the panel's built-in set (see the panel's
            // Icon.vue); add-ons cannot register their own SVGs.
            icon: 'tag',
            route: 'panel.example-addon.index',
            permission: self::PERMISSION,
        ));
    }

    public function settingsNavigation(NavigationRegistry $registry): void
    {
        // Same registry mechanism as the main sidebar, driving the Settings
        // sidebar instead. An add-on can create its own group (as here) or add
        // items to a first-party one (e.g. 'general').
        $registry->group('example-addon', 'example-addon::example.settings_group');
        $registry->addItem('example-addon', new NavigationItem(
            key: 'example-addon-settings',
            label: 'example-addon::example.settings_label',
            route: 'panel.settings.example-addon.index',
            permission: self::PERMISSION,
        ));
    }

    public function routes(): ?Closure
    {
        return function (): void {
            Route::middleware('can:'.self::PERMISSION)->group(function (): void {
                Route::get('example-addon', function () {
                    // Static demo rows shaped the way a table page shares them: plain
                    // attributes for the columns, plus an _actions map of per-row URLs
                    // the ellipsis actions dispatch to. A real add-on queries its own
                    // models here.
                    $widgets = collect([
                        ['id' => 1, 'name' => 'Anti-Gravity Widget', 'status' => 'active', 'sales' => 132],
                        ['id' => 2, 'name' => 'Self-Sealing Stem Bolt', 'status' => 'active', 'sales' => 87],
                        ['id' => 3, 'name' => 'Left-Handed Hammer', 'status' => 'archived', 'sales' => 12],
                    ])->map(fn (array $widget) => [
                        ...$widget,
                        '_actions' => ['ping' => route('panel.example-addon.widgets.ping', $widget['id'])],
                    ])->all();

                    return Inertia::render('example-addon::Widgets/Index', [
                        'message' => 'Hello from the example add-on! This page was registered at runtime via window.LunarPanel.registerPages(), not compiled into the panel.',
                        'widgets' => $widgets,
                    ]);
                })->name('panel.example-addon.index');

                Route::get('example-addon/widgets/{widget}/ping', fn (int $widget) => back()
                    ->with('success', "Widget {$widget} pinged (example table row action)."))
                    ->name('panel.example-addon.widgets.ping');

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

                // The settings screen registered by settingsNavigation() above. Values
                // live in the session so the demo round-trips without a table; a real
                // add-on would persist to its own model or config instead.
                Route::prefix('settings/example-addon')
                    ->name('panel.settings.example-addon.')
                    ->group(function (): void {
                        Route::get('/', fn (Request $request) => Inertia::render('example-addon::Settings/Index', [
                            'settings' => $request->session()->get('example-addon.settings', [
                                'webhook_url' => '',
                                'ping_enabled' => true,
                            ]),
                            'urls' => ['update' => route('panel.settings.example-addon.update')],
                        ]))->name('index');

                        Route::post('/', function (Request $request) {
                            $request->session()->put('example-addon.settings', $request->validate([
                                'webhook_url' => ['nullable', 'url'],
                                'ping_enabled' => ['required', 'boolean'],
                            ]));

                            return back()->with('success', __('example-addon::example.settings_saved'));
                        })->name('update');
                    });
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

        // The canonical slot example (spec 0049/0057): the product edit page
        // deliberately ships no SEO section — an add-on injects one into the
        // content-adjacent zone between the content cluster and the variants
        // block. The zone passes the page's product to the component.
        $registry->add(new Slot(
            zone: 'products.edit:content:after',
            component: 'example-addon::SeoCard',
        ));
    }

    public function tableExtensions(): array
    {
        return ['customers.index' => ExampleTableExtension::class];
    }

    /**
     * Opt this add-on's lang groups into the panel's translations endpoint,
     * so its Vue pages can translate through vue-i18n with
     * `t('example-addon::example.title')`. Locales the add-on doesn't ship
     * fall back to the app fallback locale per namespace.
     */
    public function langNamespaces(): array
    {
        return ['example-addon'];
    }
}
