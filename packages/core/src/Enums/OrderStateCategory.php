<?php

namespace Lunar\Core\Enums;

enum OrderStateCategory
{
    case Pending;
    case Active;
    case Complete;
    case Blocked;
    case Failed;
}
