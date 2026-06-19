<?php

namespace Lunar\Core\Enums;

/**
 * What an order notification is constructed with, which also decides where it
 * can be sent from. Order-scoped notifications receive the `Order` and send
 * from the order; fulfilment-scoped ones receive a `Fulfilment` (to read a
 * specific parcel's tracking and lines) and send from that fulfilment.
 */
enum NotificationScope
{
    case Order;
    case Fulfilment;
}
