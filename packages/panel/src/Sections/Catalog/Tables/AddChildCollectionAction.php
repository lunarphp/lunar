<?php

namespace Lunar\Panel\Sections\Catalog\Tables;

use Lunar\Panel\Support\Position;
use Lunar\Panel\Tables\TableAction;

class AddChildCollectionAction extends TableAction
{
    public function key(): string
    {
        return 'add-child';
    }

    public function label(): string
    {
        return __('panel::collections.action_add_child');
    }

    public function icon(): ?string
    {
        return 'plus';
    }

    public function position(): Position
    {
        return Position::priority(20);
    }

    public function method(): string
    {
        return 'get';
    }

    public function url(mixed $record = null): ?string
    {
        return $record ? route('panel.collections.create', ['parent' => $record->getKey()]) : null;
    }
}
