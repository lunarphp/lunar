<?php

namespace Lunar\Admin\Support\Pages;

use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;

abstract class BaseListRecords extends ListRecords
{
    use Concerns\ExtendsHeaderActions;
    use \Lunar\Admin\Support\Concerns\CallsHooks;
}