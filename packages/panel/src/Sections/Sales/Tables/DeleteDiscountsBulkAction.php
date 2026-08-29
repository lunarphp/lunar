<?php

namespace Lunar\Panel\Sections\Sales\Tables;

use Lunar\Panel\Support\Position;
use Lunar\Panel\Tables\TableBulkAction;

class DeleteDiscountsBulkAction extends TableBulkAction
{
    public function key(): string
    {
        return 'delete';
    }

    public function label(): string
    {
        return __('panel::discounts.bulk_delete');
    }

    public function icon(): ?string
    {
        return 'trash';
    }

    public function position(): Position
    {
        return Position::priority(90);
    }

    public function confirmationMessage(): ?string
    {
        return __('panel::discounts.confirm_bulk_delete');
    }

    public function url(): ?string
    {
        return route('panel.discounts.bulk-destroy');
    }
}
