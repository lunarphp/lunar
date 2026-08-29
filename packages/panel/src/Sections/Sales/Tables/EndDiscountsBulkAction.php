<?php

namespace Lunar\Panel\Sections\Sales\Tables;

use Lunar\Panel\Support\Position;
use Lunar\Panel\Tables\TableBulkAction;

/**
 * Sets ends_at to now on the selection — switching discounts off without
 * losing the records or the reporting that hangs off them.
 */
class EndDiscountsBulkAction extends TableBulkAction
{
    public function key(): string
    {
        return 'end-now';
    }

    public function label(): string
    {
        return __('panel::discounts.bulk_end_now');
    }

    public function icon(): ?string
    {
        return 'power';
    }

    public function position(): Position
    {
        return Position::priority(10);
    }

    public function confirmationMessage(): ?string
    {
        return __('panel::discounts.confirm_bulk_end');
    }

    public function url(): ?string
    {
        return route('panel.discounts.bulk-end');
    }
}
