<?php

namespace LunarPanelExample\Tables;

use Lunar\Panel\Tables\TableBulkAction;

/**
 * A bulk action injected into the first-party customers table. Registering any
 * bulk action is what makes the table's selection checkboxes appear.
 */
class PingBulkAction extends TableBulkAction
{
    public function key(): string
    {
        return 'example-bulk-ping';
    }

    public function label(): string
    {
        return 'Ping selected (Example)';
    }

    public function icon(): ?string
    {
        return 'refresh';
    }

    public function method(): string
    {
        return 'post';
    }

    public function url(): ?string
    {
        return route('panel.example-addon.bulk-ping');
    }
}
