<?php

namespace Lunar\Panel\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Models\Staff;
use Lunar\Panel\Dashboard\DashboardRange;
use Lunar\Panel\Dashboard\LayoutResolver;
use Lunar\Panel\Models\StaffPreference;
use Lunar\Panel\PanelManager;

class DashboardController
{
    public const PREFERENCE_KEY = 'dashboard';

    public function __invoke(Request $request, PanelManager $panel, LayoutResolver $layouts): Response
    {
        /** @var Staff|null $staff */
        $staff = $panel->user();

        $stored = $staff ? StaffPreference::valueFor($staff, self::PREFERENCE_KEY) : null;

        $range = DashboardRange::fromValue(
            $request->query('range', $stored['range'] ?? null),
        );

        $layout = $layouts->resolve($panel->widgets()->for($staff), $stored['widgets'] ?? null);

        // Each visible widget's payload is a deferred prop in its own group,
        // so widgets load in parallel after first paint (behind skeletons) and
        // a slow widget never blocks the others. Hidden widgets load nothing.
        $widgetData = [];

        foreach ($layout as $entry) {
            if (! $entry['visible']) {
                continue;
            }

            $widget = $entry['widget'];

            $widgetData[$widget->key()] = Inertia::defer(
                fn () => $widget->data($range),
                'widget:'.$widget->key(),
            );
        }

        return Inertia::render('Dashboard', [
            'range' => $range->value,
            'widgets' => array_map(
                fn (array $entry) => [...$entry['widget']->toArray(), 'visible' => $entry['visible']],
                $layout,
            ),
            'widgetData' => $widgetData,
            'urls' => [
                'preferences' => route('panel.dashboard.preferences.update'),
            ],
        ]);
    }
}
