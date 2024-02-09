<?php

namespace Lunar\Admin\Support\Pages;

use Filament\Resources\Pages\ViewRecord;

abstract class BaseViewRecord extends ViewRecord
{
    use Concerns\ExtendsHeaderActions;
    use \Lunar\Admin\Support\Concerns\CallsHooks;
}
