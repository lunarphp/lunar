<?php

namespace Lunar\Shipping\Panel\Tables;

use Lunar\Panel\Support\Position;
use Lunar\Panel\Tables\TableAction;

class EditMethodAction extends TableAction
{
    public function key(): string
    {
        return 'edit';
    }

    public function label(): string
    {
        return __('panel::common.edit');
    }

    public function icon(): ?string
    {
        return 'edit';
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
        return $record ? route('panel.settings.shipping.methods.edit', $record) : null;
    }
}
