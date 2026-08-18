<?php

namespace LunarPanelExample\Actions;

use Lunar\Panel\Actions\PageAction;

/**
 * A listing-page header action (on customers.index). It receives no record
 * context, resolving a static URL, and collapses into the header ellipsis.
 */
class ImportPageAction extends PageAction
{
    public function key(): string
    {
        return 'example-import';
    }

    public function label(): string
    {
        return 'Import (Example)';
    }

    public function icon(): ?string
    {
        return 'download';
    }

    public function url(mixed $context = null): ?string
    {
        return route('panel.example-addon.import');
    }
}
