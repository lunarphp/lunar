<?php

namespace Lunar\Admin\Support\Pages;

use Filament\Resources\Pages\ManageRelatedRecords;
use Lunar\Admin\Support\Concerns\CallsHooks;
use Lunar\Admin\Support\Pages\Concerns\ExtendsFooterWidgets;
use Lunar\Admin\Support\Pages\Concerns\ExtendsHeaderActions;
use Lunar\Admin\Support\Pages\Concerns\ExtendsHeaderWidgets;
use Lunar\Admin\Support\Pages\Concerns\ExtendsHeadings;
use Lunar\Admin\Support\Pages\Concerns\ExtendsTables;

abstract class BaseManageRelatedRecords extends ManageRelatedRecords
{
    use CallsHooks;
    use ExtendsFooterWidgets;
    use ExtendsHeaderActions;
    use ExtendsHeaderWidgets;
    use ExtendsHeadings;
    use ExtendsTables;
}
