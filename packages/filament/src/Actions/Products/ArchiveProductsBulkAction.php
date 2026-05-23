<?php

namespace Lunar\Filament\Actions\Products;

use Filament\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;
use Lunar\Filament\Actions\Concerns\ConfirmsDestructiveAction;
use Lunar\Filament\Actions\Products\Concerns\AppliesProductStatusInBulk;

class ArchiveProductsBulkAction extends BulkAction
{
    use AppliesProductStatusInBulk;
    use ConfirmsDestructiveAction;

    public static function getDefaultName(): ?string
    {
        return 'archive';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('lunar-filament::actions.products.archive.bulk_label'))
            ->icon('heroicon-o-archive-box')
            ->color('danger')
            ->requiresConfirmation()
            ->schema([
                $this->confirmToggle(__('lunar-filament::actions.products.archive.confirm.helper_text')),
            ])
            ->action(fn (Collection $records) => $this->applyProductStatusInBulk($records, 'archived'))
            ->successNotificationTitle(__('lunar-filament::actions.products.archive.notification.success'));
    }
}
