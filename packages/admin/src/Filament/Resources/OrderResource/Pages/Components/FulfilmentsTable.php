<?php

namespace Lunar\Admin\Filament\Resources\OrderResource\Pages\Components;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Lunar\Admin\Livewire\Components\TableComponent;
use Lunar\Core\Actions\Fulfilment\MergeFulfilments;
use Lunar\Core\Actions\Fulfilment\ReturnFulfilment;
use Lunar\Core\Actions\Fulfilment\ShipFulfilment;
use Lunar\Core\Actions\Fulfilment\SplitFulfilment;
use Lunar\Core\Exceptions\FulfilmentException;
use Lunar\Core\Facades\Fulfilments;
use Lunar\Core\Models\Fulfilment;
use Lunar\Filament\Support\Concerns\CallsHooks;
use Spatie\ModelStates\Exceptions\CouldNotPerformTransition;

class FulfilmentsTable extends TableComponent
{
    use CallsHooks;

    public function getDefaultTable(Table $table): Table
    {
        return $table
            ->query($this->record->fulfilments()->getQuery()->with('lines.orderLine.purchasable'))
            ->columns([
                ViewColumn::make('card')
                    ->label('')
                    ->view('lunarpanel::tables.columns.fulfilment-card'),
            ])
            ->recordActions([
                $this->getShipAction(),
                $this->getSplitAction(),
                $this->getReturnAction(),
            ])
            ->toolbarActions([
                $this->getMergeAction(),
            ])
            ->emptyStateHeading(__('lunarpanel::order.fulfilments.empty'));
    }

    public function table(Table $table): Table
    {
        return self::callStaticLunarHook('extendTable', $this->getDefaultTable($table));
    }

    protected function getShipAction(): Action
    {
        return Action::make('ship')
            ->label(__('lunarpanel::order.fulfilments.actions.ship.label'))
            ->modalHeading(__('lunarpanel::order.fulfilments.actions.ship.modal_heading'))
            ->icon('heroicon-o-truck')
            ->color('success')
            ->visible(fn (Fulfilment $record) => ShipFulfilment::canRun($record))
            ->schema([
                TextInput::make('tracking_number')
                    ->label(__('lunarpanel::order.fulfilments.fields.tracking_number')),
                TextInput::make('tracking_url')
                    ->label(__('lunarpanel::order.fulfilments.fields.tracking_url'))
                    ->url(),
                TextInput::make('shipping_method')
                    ->label(__('lunarpanel::order.fulfilments.fields.shipping_method')),
            ])
            ->action(fn (array $data, Fulfilment $record) => $this->runFulfilmentAction(
                fn () => Fulfilments::ship($record, array_filter($data)),
                'ship',
            ));
    }

    protected function getSplitAction(): Action
    {
        return Action::make('split')
            ->label(__('lunarpanel::order.fulfilments.actions.split.label'))
            ->modalHeading(__('lunarpanel::order.fulfilments.actions.split.modal_heading'))
            ->icon('heroicon-o-scissors')
            ->visible(fn (Fulfilment $record) => SplitFulfilment::canRun($record) && $record->lines->sum('quantity') > 1)
            ->schema(fn (Fulfilment $record) => $record->lines->map(
                fn ($line) => TextInput::make('qty_'.$line->order_line_id)
                    ->label($line->orderLine?->description ?? '#'.$line->order_line_id)
                    ->helperText(__('lunarpanel::order.fulfilments.fields.outstanding', ['count' => $line->quantity]))
                    ->numeric()
                    ->minValue(0)
                    ->maxValue($line->quantity)
                    ->default(0)
            )->all())
            ->action(function (array $data, Fulfilment $record) {
                $moves = collect($data)
                    ->filter(fn ($qty) => (int) $qty > 0)
                    ->mapWithKeys(fn ($qty, $key) => [(int) str_replace('qty_', '', $key) => (int) $qty])
                    ->all();

                $this->runFulfilmentAction(
                    fn () => Fulfilments::split($record, $moves),
                    'split',
                );
            });
    }

    protected function getReturnAction(): Action
    {
        return Action::make('return')
            ->label(__('lunarpanel::order.fulfilments.actions.return.label'))
            ->icon('heroicon-o-arrow-uturn-left')
            ->color('warning')
            ->requiresConfirmation()
            ->visible(fn (Fulfilment $record) => ReturnFulfilment::canRun($record))
            ->action(fn (Fulfilment $record) => $this->runFulfilmentAction(
                fn () => Fulfilments::return($record),
                'return',
            ));
    }

    protected function getMergeAction(): BulkAction
    {
        return BulkAction::make('merge')
            ->label(__('lunarpanel::order.fulfilments.actions.merge.label'))
            ->modalHeading(__('lunarpanel::order.fulfilments.actions.merge.modal_heading'))
            ->icon('heroicon-o-arrows-pointing-in')
            ->requiresConfirmation()
            ->action(function (EloquentCollection $records) {
                // Oldest selected parcel is the target; the rest fold into it.
                $sorted = $records->sortBy('id')->values();
                $target = $sorted->first();
                $sources = $sorted->slice(1)->values();

                if (! $target || ! MergeFulfilments::canRun($target, $sources)) {
                    $this->fulfilmentFailure('merge');

                    return;
                }

                $this->runFulfilmentAction(
                    fn () => Fulfilments::merge($target, $sources),
                    'merge',
                );
            });
    }

    /**
     * Run a fulfilment operation, surfacing domain/transition failures as a
     * notification rather than an unhandled exception.
     */
    protected function runFulfilmentAction(callable $callback, string $key): void
    {
        try {
            $callback();
        } catch (FulfilmentException|CouldNotPerformTransition $e) {
            $this->fulfilmentFailure($key, $e->getMessage());

            return;
        }

        Notification::make()
            ->success()
            ->title(__('lunarpanel::order.fulfilments.actions.'.$key.'.notification.success'))
            ->send();

        $this->dispatch('fulfilments-updated');
    }

    protected function fulfilmentFailure(string $key, ?string $message = null): void
    {
        Notification::make()
            ->danger()
            ->title($message ?: __('lunarpanel::order.fulfilments.actions.'.$key.'.notification.error'))
            ->send();
    }
}
