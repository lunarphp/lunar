<?php

namespace Lunar\Panel\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Lunar\Core\Models\Staff;
use Lunar\Panel\Dashboard\DashboardRange;
use Lunar\Panel\Dashboard\Widget;
use Lunar\Panel\Models\StaffPreference;
use Lunar\Panel\PanelManager;

/**
 * Persists the staff member's own dashboard layout: widget order (array
 * order), per-widget visibility, and the selected range. Keys that don't
 * match a registered, permitted widget are dropped rather than rejected, so
 * a stale client after an add-on uninstall still saves cleanly.
 */
class DashboardPreferencesController
{
    public function update(Request $request, PanelManager $panel): Response
    {
        $payload = $request->validate([
            'range' => ['required', Rule::enum(DashboardRange::class)],
            'widgets' => ['present', 'array'],
            'widgets.*.key' => ['required', 'string'],
            'widgets.*.visible' => ['required', 'boolean'],
        ]);

        $staff = $this->staff($panel);

        $known = array_map(
            fn (Widget $widget) => $widget->key(),
            $panel->widgets()->for($staff),
        );

        $widgets = collect($payload['widgets'])
            ->filter(fn (array $entry) => in_array($entry['key'], $known, true))
            ->unique('key')
            ->map(fn (array $entry) => ['key' => $entry['key'], 'visible' => (bool) $entry['visible']])
            ->values()
            ->all();

        StaffPreference::put($staff, DashboardController::PREFERENCE_KEY, [
            'range' => $payload['range'],
            'widgets' => $widgets,
        ]);

        return response()->noContent();
    }

    public function destroy(PanelManager $panel): Response
    {
        StaffPreference::forget($this->staff($panel), DashboardController::PREFERENCE_KEY);

        return response()->noContent();
    }

    protected function staff(PanelManager $panel): Staff
    {
        /** @var Staff $staff */
        $staff = $panel->user();

        return $staff;
    }
}
