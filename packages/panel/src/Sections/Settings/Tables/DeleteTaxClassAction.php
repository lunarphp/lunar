<?php

namespace Lunar\Panel\Sections\Settings\Tables;

use Lunar\Core\Models\TaxClass;
use Lunar\Panel\Support\Position;
use Lunar\Panel\Tables\TableAction;

class DeleteTaxClassAction extends TableAction
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
        return __('panel::tax_classes.confirm_delete_tax_class');
    }

    /** The default tax class is protected, so it carries no delete action. */
    public function url(mixed $record = null): ?string
    {
        if (! $record instanceof TaxClass || $record->default) {
            return null;
        }

        return route('panel.settings.tax-classes.destroy', $record);
    }
}
