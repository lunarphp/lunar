<?php

namespace Lunar\Filament\Actions\Orders;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Lunar\Core\Actions\Orders\CaptureOrder;
use Lunar\Core\Exceptions\OrderActionException;
use Lunar\Core\Models\Order;
use Lunar\Filament\Actions\Concerns\ConfirmsDestructiveAction;
use Lunar\Filament\Actions\Concerns\InteractsWithTransactions;

class CaptureOrderAction extends Action
{
    use ConfirmsDestructiveAction;
    use InteractsWithTransactions;

    public static function getDefaultName(): ?string
    {
        return 'capture';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('lunar-filament::actions.orders.capture.label'))
            ->modalHeading(__('lunar-filament::actions.orders.capture.modal_heading'))
            ->modalSubmitActionLabel(__('lunar-filament::actions.orders.capture.label'))
            ->modalWidth('lg')
            ->icon('heroicon-o-credit-card')
            ->schema(fn (Order $record) => $this->getCaptureSchema($record))
            ->action(fn (array $data, Order $record) => $this->performCapture($data, $record))
            ->visible(fn (?Order $record = null) => $record !== null && CaptureOrder::canRun($record))
            ->successNotificationTitle(__('lunar-filament::actions.orders.capture.notification.success'))
            ->failureNotificationTitle(__('lunar-filament::actions.orders.capture.notification.error'));
    }

    /**
     * @return array<int, mixed>
     */
    protected function getCaptureSchema(Order $record): array
    {
        $intents = CaptureOrder::intents($record);

        return [
            $this->transactionSelect(
                $intents,
                fn ($intent) => "{$intent->format('amount')} - {$intent->driver}",
            ),
            $this->amountInput($record->decimal('total'), $record),
            $this->confirmToggle(__('lunar-filament::actions.orders.capture.confirm.helper_text')),
        ];
    }

    protected function performCapture(array $data, Order $record): void
    {
        try {
            $result = $record->capture(
                transactionId: $data['transaction'],
                amount: $data['amount'],
            );
        } catch (OrderActionException $exception) {
            $this->failureNotification(
                fn () => Notification::make('capture_failure')->color('danger')->title($exception->getMessage())
            );

            $this->failure();
            $this->halt();

            return;
        }

        if (! $result->success) {
            $this->failureNotification(
                fn () => Notification::make('capture_failure')->color('danger')->title($result->message)
            );

            $this->failure();
            $this->halt();

            return;
        }

        $this->success();
    }
}
