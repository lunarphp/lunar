<?php

namespace Lunar\Admin\Support\Pages;

use Filament\Resources\Pages\ViewRecord;
use Lunar\Admin\Support\Concerns\CallsHooks;
use Lunar\Admin\Support\Pages\Concerns\ExtendsFooterWidgets;
use Lunar\Admin\Support\Pages\Concerns\ExtendsHeaderActions;
use Lunar\Admin\Support\Pages\Concerns\ExtendsHeaderWidgets;
use Lunar\Admin\Support\Pages\Concerns\ExtendsHeadings;
use Lunar\Admin\Support\Pages\Concerns\ExtendsInfolist;

abstract class BaseViewRecord extends ViewRecord
{
    use CallsHooks;
    use ExtendsFooterWidgets;
    use ExtendsHeaderActions;
    use ExtendsHeaderWidgets;
    use ExtendsHeadings;
    use ExtendsInfolist;
}
