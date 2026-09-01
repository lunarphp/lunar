<?php

namespace Lunar\Panel\Sections\Sales\Tables;

use Lunar\Panel\Support\Position;
use Lunar\Panel\Tables\TableAction;

class ViewOrderAction extends TableAction
{
    public function key(): string
    {
        return 'view';
    }

    public function label(): string
    {
        return __('panel::orders.view_order');
    }

    public function icon(): ?string
    {
        return 'eye';
    }

    public function position(): Position
    {
        return Position::priority(10);
    }

    public function method(): string
    {
        return 'get';
    }

    public function url(mixed $record = null): ?string
    {
        return $record ? route('panel.orders.show', $record) : null;
    }
}
