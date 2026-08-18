<?php

namespace Lunar\Tests\Panel\Fixtures\Customers\Actions;

use Lunar\Panel\Actions\PageAction;
use Lunar\Panel\Support\Position;

/**
 * A fixture record-page action proving an add-on can inject a header action
 * that resolves a per-record URL from the route-bound model.
 */
class ImpersonatePageAction extends PageAction
{
    public function key(): string
    {
        return 'impersonate';
    }

    public function label(): string
    {
        return 'Impersonate';
    }

    public function position(): Position
    {
        return Position::first();
    }

    public function url(mixed $context = null): ?string
    {
        return $context ? route('panel.customers.edit', $context) : null;
    }
}
