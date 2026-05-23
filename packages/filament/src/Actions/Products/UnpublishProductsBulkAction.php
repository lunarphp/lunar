<?php

namespace Lunar\Filament\Actions\Products;

use Filament\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;
use Lunar\Filament\Actions\Concerns\ConfirmsDestructiveAction;
use Lunar\Filament\Actions\Products\Concerns\AppliesProductStatusInBulk;

class UnpublishProductsBulkAction extends BulkAction
{
    use AppliesProductStatusInBulk;
    use ConfirmsDestructiveAction;

    public static function getDefaultName(): ?string
    {
        return 'unpublish';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('lunar-filament::actions.products.unpublish.bulk_label'))
            ->icon('heroicon-o-eye-slash')
            ->color('warning')
            ->requiresConfirmation()
            ->schema([
                $this->confirmToggle(__('lunar-filament::actions.products.unpublish.confirm.helper_text')),
            ])
            ->action(fn (Collection $records) => $this->applyProductStatusInBulk($records, 'draft'))
            ->successNotificationTitle(__('lunar-filament::actions.products.unpublish.notification.success'));
    }
}
