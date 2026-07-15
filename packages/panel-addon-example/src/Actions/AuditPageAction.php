<?php

namespace LunarPanelExample\Actions;

use Lunar\Panel\Actions\PageAction;

/**
 * A record-page header action (on customers.edit). It builds a per-record URL
 * from the route-bound customer passed as context.
 */
class AuditPageAction extends PageAction
{
    public function key(): string
    {
        return 'example-audit';
    }

    public function label(): string
    {
        return 'Audit log (Example)';
    }

    public function icon(): ?string
    {
        return 'fileText';
    }

    public function url(mixed $context = null): ?string
    {
        return $context ? route('panel.example-addon.audit', $context) : null;
    }
}
