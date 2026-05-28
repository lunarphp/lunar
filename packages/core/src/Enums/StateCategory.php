<?php

namespace Lunar\Core\Enums;

enum StateCategory
{
    case Pending;
    case Active;
    case Complete;
    case Blocked;
    case Failed;
}
