<?php

namespace Lunar\Panel\Sections\Catalog\Tables;

use Lunar\Core\Models\Product;
use Lunar\Panel\Support\Position;
use Lunar\Panel\Tables\TableAction;

class DeleteProductAction extends TableAction
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
        return __('panel::products.confirm_delete');
    }

    /**
     * Products with order history are protected — archive instead — so they
     * carry no delete action. The index controller stamps the flag per page;
     * anywhere else falls back to the live check, with the server guard as
     * the final backstop.
     */
    public function url(mixed $record = null): ?string
    {
        if (! $record instanceof Product) {
            return null;
        }

        $protected = $record->getAttribute('has_order_history') ?? $record->hasOrderHistory();

        return $protected ? null : route('panel.products.destroy', $record);
    }
}
