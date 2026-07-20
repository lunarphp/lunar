<?php

namespace Lunar\Panel\Sections\Settings\Tables;

use Lunar\Core\Models\Region;
use Lunar\Panel\Support\Position;
use Lunar\Panel\Tables\TableAction;

class DeleteRegionAction extends TableAction
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
        return __('panel::regions.confirm_delete');
    }

    /** The default region is protected, so it carries no delete action. */
    public function url(mixed $record = null): ?string
    {
        if (! $record instanceof Region || $record->default) {
            return null;
        }

        return route('panel.settings.regions.destroy', $record);
    }
}
