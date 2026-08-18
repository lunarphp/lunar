<?php

namespace Lunar\Panel\Sections\Settings\Tables;

use Lunar\Core\Support\Facades\LunarAccessControl;
use Lunar\Panel\Support\Position;
use Lunar\Panel\Tables\TableAction;
use Spatie\Permission\Models\Role;

class DeleteRoleAction extends TableAction
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
        return __('panel::roles.confirm_delete');
    }

    /** First-party roles and roles held by staff are protected, so they carry no delete action. */
    public function url(mixed $record = null): ?string
    {
        if (! $record instanceof Role || in_array($record->name, LunarAccessControl::getBaseRoles(), true)) {
            return null;
        }

        if ($record->users()->exists()) {
            return null;
        }

        return route('panel.settings.roles.destroy', $record);
    }
}
