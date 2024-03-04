<?php

namespace Lunar\Admin\Support\Pages;

use Filament\Resources\Pages\ManageRelatedRecords;

abstract class BaseManageRelatedRecords extends ManageRelatedRecords
{
    use Concerns\ExtendsFooterWidgets;
    use Concerns\ExtendsHeaderActions;
    use Concerns\ExtendsHeaderWidgets;
    use Concerns\ExtendsHeadings;
    use \Lunar\Admin\Support\Concerns\CallsHooks;
}
