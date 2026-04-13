<?php

namespace Lunar\Admin\Support\Actions\Orders;

use Filament\Actions\BulkAction;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Collection;
use Lunar\Admin\Support\Actions\Traits\UpdatesOrderStatus;
use Lunar\Facades\DB;

class UpdateStatusBulkAction extends BulkAction
{
    use UpdatesOrderStatus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(
            __('lunarpanel::actions.orders.update_status.label')
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
