<?php

namespace LunarPanelExample\Search;

use Lunar\Panel\Search\SearchCommand;
use Lunar\Panel\Support\Position;

/**
 * A quick action contributed by an add-on: a labelled destination the palette
 * offers alongside the first-party create verbs.
 */
class PingWidgetsCommand extends SearchCommand
{
    public function key(): string
    {
        return 'example-addon.widgets';
    }

    public function label(): string
    {
        return __('example-addon::example.search_command');
    }

    public function icon(): string
    {
        return 'tag';
    }

    public function url(): string
    {
        return route('panel.example-addon.index');
    }

    public function permission(): string
    {
        return 'sales:manage-customers';
    }

    public function position(): Position
    {
        return Position::last();
    }
}
