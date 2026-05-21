<?php

namespace Lunar\Admin\Support\Resources;

use Filament\Resources\Resource;
use Lunar\Admin\Support\Concerns\CallsHooks;
use Lunar\Admin\Support\Resources\Concerns\ExtendsForms;
use Lunar\Admin\Support\Resources\Concerns\ExtendsPages;
use Lunar\Admin\Support\Resources\Concerns\ExtendsRelationManagers;
use Lunar\Admin\Support\Resources\Concerns\ExtendsSubnavigation;
use Lunar\Admin\Support\Resources\Concerns\ExtendsTables;
use Lunar\Admin\Support\Resources\Concerns\HasLunarPermissions;
use Lunar\Admin\Support\Resources\Concerns\HasScoutGlobalSearch;
use Lunar\Admin\Support\Resources\Concerns\ResolvesModelContract;

class BaseResource extends Resource
{
    use CallsHooks;
    use ExtendsForms;
    use ExtendsPages;
    use ExtendsRelationManagers;
    use ExtendsSubnavigation;
    use ExtendsTables;
    use HasLunarPermissions;
    use HasScoutGlobalSearch;
    use ResolvesModelContract;
}
