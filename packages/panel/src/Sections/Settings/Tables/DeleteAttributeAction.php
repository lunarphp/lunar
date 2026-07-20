<?php

namespace Lunar\Panel\Sections\Settings\Tables;

use Lunar\Core\Models\Attribute;
use Lunar\Panel\Support\Position;
use Lunar\Panel\Tables\TableAction;

class DeleteAttributeAction extends TableAction
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
        return __('panel::attributes_settings.confirm_delete');
    }

    /** System attributes are protected, so they carry no delete action. */
    public function url(mixed $record = null): ?string
    {
        if (! $record instanceof Attribute || $record->system) {
            return null;
        }

        return route('panel.settings.attributes.destroy', $record);
    }
}
