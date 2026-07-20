<?php

namespace Lunar\Panel\Sections\Catalog\Tables;

use Lunar\Panel\Support\Position;
use Lunar\Panel\Tables\TableAction;

class DeleteBrandAction extends TableAction
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
        return __('panel::brands.confirm_delete_brand');
    }

    public function url(mixed $record = null): ?string
    {
        return $record ? route('panel.brands.destroy', $record) : null;
    }
}
