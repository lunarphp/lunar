<?php

namespace Lunar\Panel\Dashboard;

/**
 * Reconciles a staff member's stored dashboard layout with the live widget
 * registry: stored keys that are no longer registered (or no longer
 * permitted) drop out, and newly registered widgets append in their default
 * order with their default visibility.
 */
class LayoutResolver
{
    /**
     * @param  Widget[]  $widgets  the registered widgets available to the staff member, in default order
     * @param  array<int, array{key: string, visible: bool}>|null  $stored
     * @return array<int, array{widget: Widget, visible: bool}> display order
     */
    public function resolve(array $widgets, ?array $stored): array
    {
        $byKey = [];

        foreach ($widgets as $widget) {
            $byKey[$widget->key()] = $widget;
        }

        $layout = [];

        foreach ($stored ?? [] as $entry) {
            $widget = $byKey[$entry['key']] ?? null;

            if ($widget === null) {
                continue;
            }

            $layout[] = ['widget' => $widget, 'visible' => (bool) $entry['visible']];
            unset($byKey[$entry['key']]);
        }

        foreach ($byKey as $widget) {
            $layout[] = ['widget' => $widget, 'visible' => $widget->visibleByDefault()];
        }

        return $layout;
    }
}
