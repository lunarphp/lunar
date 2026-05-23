<?php

namespace Lunar\Filament\Actions\Orders;

use Filament\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;
use Lunar\Core\Actions\Orders\MarkOrderAsShipped;
use Lunar\Core\Facades\DB;

class MarkOrdersAsShippedBulkAction extends BulkAction
{
    public static function getDefaultName(): ?string
    {
        return 'mark_as_shipped';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('lunar-filament::actions.orders.mark_as_shipped.bulk_label'))
            ->icon('heroicon-o-truck')
            ->color('success')
            ->action(function (Collection $records): void {
                DB::beginTransaction();

                foreach ($records as $record) {
                    MarkOrderAsShipped::run($record);
                }

                DB::commit();
            })
            ->successNotificationTitle(__('lunar-filament::actions.orders.mark_as_shipped.notification.bulk_success'));
    }
}
