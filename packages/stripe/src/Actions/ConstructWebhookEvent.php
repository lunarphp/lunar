<?php

namespace Lunar\Stripe\Actions;

use Lunar\Stripe\Concerns\ConstructsWebhookEvent;
use Stripe\Webhook;

class ConstructWebhookEvent implements ConstructsWebhookEvent
{
    public function constructEvent(string $jsonPayload, string $signature, string $secret)
    {
        return Webhook::constructEvent(
            $jsonPayload, $signature, $secret
        );
    }
}
