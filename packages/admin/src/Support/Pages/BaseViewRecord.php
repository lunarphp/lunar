<?php

namespace Lunar\Admin\Support\Pages;

use Filament\Resources\Pages\ViewRecord;

abstract class BaseViewRecord extends ViewRecord
{
    use Concerns\ExtendsFooterWidgets;
    use Concerns\ExtendsHeaderActions;
    use Concerns\ExtendsHeaderWidgets;
    use Concerns\ExtendsHeadings;
    use Concerns\ExtendsInfolist;
    use \Lunar\Admin\Support\Concerns\CallsHooks;
    use \Lunar\Admin\Support\Concerns\CallsHooks;
}
