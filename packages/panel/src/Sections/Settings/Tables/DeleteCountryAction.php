<?php

namespace Lunar\Panel\Sections\Settings\Tables;

use Lunar\Core\Models\Country;
use Lunar\Panel\Support\Position;
use Lunar\Panel\Tables\TableAction;

class DeleteCountryAction extends TableAction
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
        return __('panel::countries.confirm_delete_country');
    }

    /** Countries with states are protected, so they carry no delete action. */
    public function url(mixed $record = null): ?string
    {
        if (! $record instanceof Country || (int) $record->getAttribute('states_count') > 0) {
            return null;
        }

        return route('panel.settings.countries.destroy', $record);
    }
}
