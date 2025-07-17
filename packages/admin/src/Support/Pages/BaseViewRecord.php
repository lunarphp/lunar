<?php

namespace Lunar\Admin\Support\Pages;

use Lunar\Admin\Support\Pages\Concerns\ExtendsFooterWidgets;
use Lunar\Admin\Support\Pages\Concerns\ExtendsHeaderActions;
use Lunar\Admin\Support\Pages\Concerns\ExtendsHeaderWidgets;
use Lunar\Admin\Support\Pages\Concerns\ExtendsHeadings;
use Lunar\Admin\Support\Pages\Concerns\ExtendsInfolist;
use Lunar\Admin\Support\Concerns\CallsHooks;
use Filament\Resources\Pages\ViewRecord;

abstract class BaseViewRecord extends ViewRecord
{
    use ExtendsFooterWidgets;
    use ExtendsHeaderActions;
    use ExtendsHeaderWidgets;
    use ExtendsHeadings;
    use ExtendsInfolist;
    use CallsHooks;
    use CallsHooks;
}
