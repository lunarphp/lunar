<?php

namespace Lunar\Panel\Sections\Catalog\Tables;

use Lunar\Core\States\ProductType\Draft;
use Lunar\Panel\Support\Position;
use Lunar\Panel\Tables\TableBulkAction;

class SetProductTypesDraftBulkAction extends TableBulkAction
{
    public function key(): string
    {
        return 'set-draft';
    }

    public function label(): string
    {
        return __('panel::product-types.bulk_set_draft');
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
        return route('panel.product-types.bulk-status', ['status' => Draft::$name]);
    }
}
