<?php

namespace Lunar\Panel\Sections\Catalog\Tables;

use Lunar\Core\States\Brand\Draft;
use Lunar\Panel\Support\Position;
use Lunar\Panel\Tables\TableBulkAction;

class SetBrandsDraftBulkAction extends TableBulkAction
{
    public function key(): string
    {
        return 'set-draft';
    }

    public function label(): string
    {
        return __('panel::brands.bulk_set_draft');
    }

    public function icon(): ?string
    {
        return 'edit';
    }

    public function position(): Position
    {
        return Position::priority(20);
    }

    public function method(): string
    {
        return 'post';
    }

    public function url(): ?string
    {
        return route('panel.brands.bulk-status', ['status' => Draft::$name]);
    }
}
