<?php

namespace LunarPanelExample\Tables;

use Lunar\Panel\Support\Position;
use Lunar\Panel\Tables\TableAction;

/**
 * A row action injected into the first-party customers table, anchored to sit
 * immediately after the built-in Edit action to demonstrate relative ordering.
 */
class PingRowAction extends TableAction
{
    public function key(): string
    {
        return 'example-ping';
    }

    public function label(): string
    {
        return 'Ping (Example)';
    }

    public function icon(): ?string
    {
        return 'refresh';
    }

    public function position(): Position
    {
        return Position::after('edit');
    }

    public function method(): string
    {
        return 'get';
    }

    public function url(mixed $record = null): ?string
    {
        return $record ? route('panel.example-addon.ping', $record) : null;
    }
}
