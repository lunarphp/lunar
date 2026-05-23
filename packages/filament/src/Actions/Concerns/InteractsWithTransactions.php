<?php

namespace Lunar\Filament\Actions\Concerns;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Collection;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\Transaction;

/**
 * Form-input building blocks shared by transaction-mutating actions
 * (`RefundOrderAction`, `CaptureOrderAction`). The trait owns only the
 * Filament-side schema fragments — domain validation and dispatch live in
 * the corresponding `Lunar\Core\Actions\Orders\*` classes.
 */
trait InteractsWithTransactions
{
    /**
     * Select input for picking a transaction from a pre-filtered collection.
     *
     * @param  Collection<int, Transaction>  $transactions
     */
    protected function transactionSelect(Collection $transactions, ?\Closure $optionFormatter = null): Select
    {
        $optionFormatter ??= fn ($transaction) => "{$transaction->amount->formatted} - {$transaction->driver}".(
            $transaction->reference ? " // {$transaction->reference}" : ''
        );

        return Select::make('transaction')
            ->label(__('lunar-filament::actions.orders.shared.transaction.label'))
            ->required()
            ->default(fn () => $transactions->first()?->id)
            ->options(fn () => $transactions->mapWithKeys(fn ($transaction) => [
                $transaction->id => $optionFormatter($transaction),
            ]))
            ->live();
    }

    /**
     * Amount input, expressed in major units, defaulted from a major-unit value.
     */
    protected function amountInput(float|int|string $defaultDecimal, Order $record): TextInput
    {
        return TextInput::make('amount')
            ->required()
            ->label(__('lunar-filament::actions.orders.shared.amount.label'))
            ->suffix(fn () => $record->currency->code)
            ->default(number_format((float) $defaultDecimal, $record->currency->decimal_places, '.', ''))
            ->live()
            ->autocomplete(false)
            ->minValue(1 / $record->currency->factor)
            ->numeric();
    }

    protected function notesInput(): Textarea
    {
        return Textarea::make('notes')
            ->label(__('lunar-filament::actions.orders.shared.notes.label'))
            ->autocomplete(false)
            ->maxLength(255);
    }
}
