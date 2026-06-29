<?php

namespace Lunar\Core\Contracts\Actions\Fulfilment;

use Lunar\Core\Models\Fulfilment;

interface ReturnsFulfilment
{
    /**
     * Mark a shipped fulfilment as returned. Independent of refunds — a
     * return never issues a refund. Pass `$notify: false` to suppress the
     * customer notification this state change would otherwise trigger.
     */
    public function execute(Fulfilment $fulfilment, bool $notify = true): Fulfilment;
}
