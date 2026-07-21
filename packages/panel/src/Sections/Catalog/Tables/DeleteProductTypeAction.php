<?php

namespace Lunar\Panel\Sections\Catalog\Tables;

use Lunar\Core\Models\ProductType;
use Lunar\Panel\Support\Position;
use Lunar\Panel\Tables\TableAction;

class DeleteProductTypeAction extends TableAction
{
    public function key(): string
    {
        return 'delete';
    }

    public function label(): string
    {
        return __('panel::common.delete');
    }

    public function icon(): ?string
    {
        return 'trash';
    }

    public function position(): Position
    {
        return Position::priority(90);
    }

    public function method(): string
    {
        return 'delete';
    }

    public function confirmationMessage(): ?string
    {
        return __('panel::product-types.confirm_delete');
    }

    /** Types with products are protected, so they carry no delete action. */
    public function url(mixed $record = null): ?string
    {
        if (! $record instanceof ProductType || (int) $record->getAttribute('products_count') > 0) {
            return null;
        }

        return route('panel.product-types.destroy', $record);
    }
}
