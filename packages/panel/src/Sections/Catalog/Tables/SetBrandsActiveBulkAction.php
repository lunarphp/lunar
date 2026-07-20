<?php

namespace Lunar\Panel\Sections\Catalog\Tables;

use Lunar\Core\States\Brand\Active;
use Lunar\Panel\Support\Position;
use Lunar\Panel\Tables\TableBulkAction;

class SetBrandsActiveBulkAction extends TableBulkAction
{
    public function key(): string
    {
        return 'set-active';
    }

    public function label(): string
    {
        return __('panel::brands.bulk_set_active');
    }

    public function icon(): ?string
    {
        return 'check';
    }

    public function position(): Position
    {
        return Position::priority(10);
    }

    public function method(): string
    {
        return 'post';
    }

    public function url(): ?string
    {
        return route('panel.brands.bulk-status', ['status' => Active::$name]);
    }
}
