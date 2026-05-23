<?php

namespace Lunar\Filament\Actions\Orders;

use Filament\Actions\BulkAction;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Collection;
use Lunar\Core\Facades\DB;
use Lunar\Filament\Support\Concerns\UpdatesOrderStatus;

class UpdateOrderStatusBulkAction extends BulkAction
{
    use UpdatesOrderStatus;

    public static function getDefaultName(): ?string
    {
        return 'update_status';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(
            __('lunar-filament::actions.orders.update_status.label')
        );

        $this->modalWidth(Width::TwoExtraLarge);

        $this->form([
            static::getStatusSelectInput(),
            static::getMailersCheckboxInput(),
            static::getAdditionalContentInput(),
            static::getAdditionalEmailInput(),
        ]);

        $this->action(
            function (Collection $records, array $data) {
                DB::beginTransaction();
                foreach ($records as $record) {
                    $this->updateStatus($record, $data);
                }
                DB::commit();
            }
        );
    }
}
