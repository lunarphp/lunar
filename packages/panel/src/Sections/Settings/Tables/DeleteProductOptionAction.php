<?php

namespace Lunar\Panel\Sections\Settings\Tables;

use Lunar\Core\Models\ProductOption;
use Lunar\Panel\Support\Position;
use Lunar\Panel\Tables\TableAction;

class DeleteProductOptionAction extends TableAction
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
        return __('panel::product_options.confirm_delete');
    }

    /** Options linked to products are protected, so they carry no delete action. */
    public function url(mixed $record = null): ?string
    {
        if (! $record instanceof ProductOption || (int) $record->getAttribute('products_count') > 0) {
            return null;
        }

        return route('panel.settings.product-options.destroy', $record);
    }
}
