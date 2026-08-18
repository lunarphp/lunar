<?php

namespace Lunar\Panel\Dashboard;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Log;
use Lunar\Panel\Support\OrderResolver;
use Lunar\Panel\Support\Position;

class WidgetRegistry
{
    /** @var array<int, class-string<Widget>> */
    protected array $widgetClasses = [];

    /** @param class-string<Widget> $widgetClass */
    public function add(string $widgetClass): static
    {
        $this->widgetClasses[] = $widgetClass;

        return $this;
    }

    /**
     * The widgets the given user may see, in default order. Duplicate keys
     * keep the first registration and log a warning.
     *
     * @return Widget[]
     */
    public function for(?Authenticatable $user): array
    {
        $widgets = [];

        foreach ($this->widgetClasses as $class) {
            /** @var Widget $widget */
            $widget = app($class);

            if (isset($widgets[$widget->key()])) {
                Log::warning("Lunar Panel: dashboard widget key [{$widget->key()}] is already registered; [{$class}] will be ignored.");

                continue;
            }

            $widgets[$widget->key()] = $widget;
        }

        $visible = array_values(array_filter(
            $widgets,
            fn (Widget $widget) => $widget->visible($user),
        ));

        return (new OrderResolver)->sort(
            $visible,
            fn (Widget $widget): string => $widget->key(),
            fn (Widget $widget): Position => $widget->position(),
        );
    }
}
