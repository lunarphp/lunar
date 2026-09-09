<?php

namespace Lunar\Shipping\Panel\Tables;

use Lunar\Panel\Support\Position;
use Lunar\Panel\Tables\TableAction;

class DeleteExclusionListAction extends TableAction
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
        return __('shipping::exclusion_lists.confirm_delete');
    }

    public function url(mixed $record = null): ?string
    {
        return $record ? route('panel.settings.shipping.exclusion-lists.destroy', $record) : null;
    }
}
