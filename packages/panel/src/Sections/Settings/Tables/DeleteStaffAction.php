<?php

namespace Lunar\Panel\Sections\Settings\Tables;

use Lunar\Core\Models\Staff;
use Lunar\Panel\Facades\Panel;
use Lunar\Panel\Support\Position;
use Lunar\Panel\Tables\TableAction;

class DeleteStaffAction extends TableAction
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
        return __('panel::staff.confirm_delete');
    }

    /** Your own account and the last admin are protected, so they carry no delete action. */
    public function url(mixed $record = null): ?string
    {
        if (! $record instanceof Staff || $record->id === auth(Panel::guard())->id()) {
            return null;
        }

        if ($record->admin && ! Staff::query()->where('admin', true)->where('id', '!=', $record->id)->exists()) {
            return null;
        }

        return route('panel.settings.staff.destroy', $record);
    }
}
