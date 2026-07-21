<?php

namespace Lunar\Panel\Sections\Catalog\Tables;

use Lunar\Core\States\Product\Published;
use Lunar\Panel\Support\Position;
use Lunar\Panel\Tables\TableBulkAction;

class SetProductsPublishedBulkAction extends TableBulkAction
{
    public function key(): string
    {
        return 'set-published';
    }

    public function label(): string
    {
        return __('panel::products.bulk_set_published');
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
        return route('panel.products.bulk-status', ['status' => Published::$name]);
    }
}
