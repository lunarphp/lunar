<?php

namespace Lunar\Stripe\Enums;

enum CancellationReason: string
{
    case DUPLICATE = 'duplicate';
    case FRAUDULENT = 'fraudulent';
    case REQUESTED_BY_CUSTOMER = 'requested_by_customer';
    case ABANDONED = 'abandoned';
}
