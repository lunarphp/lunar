<?php

namespace Lunar\Panel\Sections\Settings\Tables;

use Lunar\Core\Models\Channel;
use Lunar\Panel\Support\Position;
use Lunar\Panel\Tables\TableAction;

class DeleteChannelAction extends TableAction
{
    public function key(): string
    {
        return 'delete';
    }

    public function label(): string
    {
        return __('Delete');
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
        return __('Are you sure you want to delete this channel?');
    }

    /** The default channel is protected, so it carries no delete action. */
    public function url(mixed $record = null): ?string
    {
        if (! $record instanceof Channel || $record->default) {
            return null;
        }

        return route('panel.settings.channels.destroy', $record);
    }
}
