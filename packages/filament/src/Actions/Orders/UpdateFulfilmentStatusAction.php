<?php

namespace Lunar\Filament\Actions\Orders;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Lunar\Core\Models\Order;
use Lunar\Core\States\Order\FulfilmentState;

/**
 * Transition an order's fulfilment_status through its state machine.
 * The OrderObserver picks up the change and recomputes order_status —
 * no manual touch on order_status here.
 *
 * Payment status is deliberately not editable through this action;
 * it is driven by payment transactions, not by an admin field.
 */
class UpdateFulfilmentStatusAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'update_fulfilment_status';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('lunar-filament::actions.orders.update_fulfilment_status.label'))
            ->icon('heroicon-o-truck')
            ->color('primary')
            ->schema([
                Select::make('fulfilment_status')
                    ->label(__('lunar-filament::actions.orders.update_fulfilment_status.field.label'))
                    ->options(fn (Order $record) => static::transitionableOptions($record))
                    ->default(fn (Order $record) => (string) $record->fulfilment_status)
                    ->required(),
            ])
            ->visible(fn (Order $record) => filled(static::transitionableOptions($record)))
            ->action(function (Order $record, array $data) {
                $target = FulfilmentState::resolveStateClass($data['fulfilment_status']);
                $record->fulfilment_status->transitionTo($target);
            })
            ->successNotificationTitle(__('lunar-filament::actions.orders.update_fulfilment_status.notification.success'));
    }

    /**
     * Legal next fulfilment states for this order, labelled. The current
     * state is excluded — there's no point offering "transition to the
     * state you're already in."
     *
     * @return array<string, string>
     */
    protected static function transitionableOptions(Order $record): array
    {
        return collect($record->fulfilment_status->transitionableStates())
            ->mapWithKeys(function (string $name) use ($record) {
                /** @var class-string<FulfilmentState>|null $class */
                $class = FulfilmentState::resolveStateClass($name);

                return $class && class_exists($class)
                    ? [$name => (new $class($record))->label()]
                    : [];
            })
            ->all();
    }
}
