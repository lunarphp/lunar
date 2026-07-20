<?php

namespace Lunar\Panel\Sections\Settings\Tables;

use Lunar\Core\Models\AttributeGroup;
use Lunar\Panel\Support\Position;
use Lunar\Panel\Tables\TableAction;

class DeleteAttributeGroupAction extends TableAction
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
        return __('panel::attribute_groups.confirm_delete');
    }

    /** System groups and groups with attributes are protected, so they carry no delete action. */
    public function url(mixed $record = null): ?string
    {
        if (! $record instanceof AttributeGroup || $record->system || $record->attributes()->exists()) {
            return null;
        }

        return route('panel.settings.attribute-groups.destroy', $record);
    }
}
