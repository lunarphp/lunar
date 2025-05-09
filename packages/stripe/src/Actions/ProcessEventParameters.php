<?php

namespace Lunar\Stripe\Actions;

use Lunar\Stripe\Concerns\ProcessesEventParameters;
use Lunar\Stripe\DataTransferObjects\EventParameters;
use Stripe\Event;

class ProcessEventParameters implements ProcessesEventParameters
{
    public function handle(Event $event): EventParameters
    {
        return new EventParameters(
            paymentIntentId: $event->data->object->id,
            orderId: $event->data->object->metadata?->order_id,
        );
    }
}
