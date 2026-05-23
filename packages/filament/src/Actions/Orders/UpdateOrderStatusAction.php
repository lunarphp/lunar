<?php

namespace Lunar\Filament\Actions\Orders;

use Filament\Actions\Action;
use Filament\Support\Enums\Width;
use Lunar\Core\Models\Order;
use Lunar\Filament\Support\Concerns\UpdatesOrderStatus;

class UpdateOrderStatusAction extends Action
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

        $this->schema(
            $this->getFormSteps()
        );

        $this->action(
            fn (Order $record, array $data) => $this->updateStatus($record, $data)
        );
    }
}
