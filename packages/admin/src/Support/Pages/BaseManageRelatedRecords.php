<?php

namespace Lunar\Admin\Support\Pages;

use Lunar\Admin\Support\Pages\Concerns\ExtendsFooterWidgets;
use Lunar\Admin\Support\Pages\Concerns\ExtendsHeaderActions;
use Lunar\Admin\Support\Pages\Concerns\ExtendsHeaderWidgets;
use Lunar\Admin\Support\Pages\Concerns\ExtendsHeadings;
use Lunar\Admin\Support\Concerns\CallsHooks;
use Filament\Resources\Pages\ManageRelatedRecords;

abstract class BaseManageRelatedRecords extends ManageRelatedRecords
{
    use ExtendsFooterWidgets;
    use ExtendsHeaderActions;
    use ExtendsHeaderWidgets;
    use ExtendsHeadings;
    use CallsHooks;
}
