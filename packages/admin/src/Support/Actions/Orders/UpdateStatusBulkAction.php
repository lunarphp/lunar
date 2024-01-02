<?php

namespace Lunar\Admin\Support\Actions\Orders;

use Filament\Tables\Actions\BulkAction;
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

        $this->modalWidth('lg');
        $this->slideOver();

        $this->form(fn () => $this->getUpdatesOrderStatusFormInputs());

        $this->action(
            function (Collection $records, array $data) {
                dd($records);
                DB::beginTransaction();
                foreach ($records as $record) {
                    $this->updateStatus($record, $data);
                }
                DB::commit();
            }
        );
    }
}
