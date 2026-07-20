<?php

namespace Lunar\Panel\Sections\Catalog\Tables;

use Lunar\Panel\Support\Position;
use Lunar\Panel\Tables\TableAction;

class DeleteCollectionAction extends TableAction
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
        return __('panel::collections.confirm_delete_collection');
    }

    public function url(mixed $record = null): ?string
    {
        // The reparent flag makes the destroy endpoint promote any children
        // to this collection's parent — exactly what the confirmation states.
        return $record ? route('panel.collections.destroy', $record).'?reparent=1' : null;
    }
}
