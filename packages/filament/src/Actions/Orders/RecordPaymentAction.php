<?php

namespace Lunar\Filament\Actions\Orders;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Lunar\Core\Actions\Orders\CaptureOrder;
use Lunar\Core\Actions\Orders\RecordPayment;
use Lunar\Core\Exceptions\OrderActionException;
use Lunar\Core\Models\Order;
use Lunar\Filament\Actions\Concerns\InteractsWithTransactions;

class RecordPaymentAction extends Action
{
    use InteractsWithTransactions;

    public static function getDefaultName(): ?string
    {
        return 'record_payment';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('lunar-filament::actions.orders.record_payment.label'))
            ->modalHeading(__('lunar-filament::actions.orders.record_payment.modal_heading'))
            ->modalSubmitActionLabel(__('lunar-filament::actions.orders.record_payment.label'))
            ->modalWidth('lg')
            ->icon('heroicon-o-banknotes')
            ->schema(fn (Order $record) => [
                $this->amountInput(RecordPayment::outstandingBalance($record) / $record->currency->factor, $record),
                TextInput::make('reference')
                    ->label(__('lunar-filament::actions.orders.record_payment.reference.label'))
                    ->autocomplete(false)
                    ->maxLength(255),
                $this->notesInput(),
            ])
            ->action(fn (array $data, Order $record) => $this->performRecordPayment($data, $record))
            // An authorized intent is settled by capturing it, not by recording a payment.
            ->visible(fn (?Order $record = null) => $record !== null && RecordPayment::canRun($record) && ! CaptureOrder::canRun($record))
            ->successNotificationTitle(__('lunar-filament::actions.orders.record_payment.notification.success'));
    }

    protected function performRecordPayment(array $data, Order $record): void
    {
        try {
            $record->recordPayment(
                (int) bcmul((string) $data['amount'], (string) $record->currency->factor),
                $data['reference'] ?? null,
                $data['notes'] ?? null,
            );
        } catch (OrderActionException $exception) {
            $this->failureNotification(
                fn () => Notification::make('record_payment_failure')->color('danger')->title($exception->getMessage())
            );

            $this->failure();
            $this->halt();

            return;
        }

        $this->success();
    }
}
