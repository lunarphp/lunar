<?php

namespace Lunar\Filament\Actions\Products;

use Filament\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;
use Lunar\Filament\Actions\Products\Concerns\AppliesProductStatusInBulk;

class PublishProductsBulkAction extends BulkAction
{
    use AppliesProductStatusInBulk;

    public static function getDefaultName(): ?string
    {
        return 'publish';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('lunar-filament::actions.products.publish.bulk_label'))
            ->icon('heroicon-o-check-badge')
            ->color('success')
            ->action(fn (Collection $records) => $this->applyProductStatusInBulk($records, 'published'))
            ->successNotificationTitle(__('lunar-filament::actions.products.publish.notification.success'));
    }
}
