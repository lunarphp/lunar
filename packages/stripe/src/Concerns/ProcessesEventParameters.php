<?php

namespace Lunar\Stripe\Concerns;

use Lunar\Stripe\DataTransferObjects\EventParameters;
use Stripe\Event;

interface ProcessesEventParameters
{
    public function handle(Event $event): EventParameters;
}
