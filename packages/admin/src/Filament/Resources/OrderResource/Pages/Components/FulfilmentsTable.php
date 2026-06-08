<?php

namespace Lunar\Admin\Filament\Resources\OrderResource\Pages\Components;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Lunar\Admin\Livewire\Components\TableComponent;
use Lunar\Core\Actions\Fulfilment\CancelFulfilment;
use Lunar\Core\Actions\Fulfilment\CreateFulfilment;
use Lunar\Core\Actions\Fulfilment\MergeFulfilments;
use Lunar\Core\Actions\Fulfilment\ReturnFulfilment;
use Lunar\Core\Actions\Fulfilment\ShipFulfilment;
use Lunar\Core\Actions\Fulfilment\SplitFulfilment;
use Lunar\Core\Exceptions\FulfilmentException;
use Lunar\Core\Facades\Fulfilments;
use Lunar\Core\Models\Fulfilment;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\Validation\Fulfilment\FulfilmentQuantity;
use Lunar\Filament\Support\Concerns\CallsHooks;
use Spatie\ModelStates\Exceptions\CouldNotPerformTransition;

class FulfilmentsTable extends TableComponent
{
    use CallsHooks;

    /**
     * Filament-compatible badge colour per fulfilment state name.
     */
    protected static function stateColor(string $name): string
    {
        return match ($name) {
            'pending' => 'gray',
            'in-progress' => 'warning',
            'shipped' => 'success',
            'cancelled', 'returned' => 'danger',
            default => 'gray',
        };
    }

    public function getDefaultTable(Table $table): Table
    {
        return $table
            ->query($this->record->fulfilments()->getQuery()->with('lines.orderLine'))
            ->columns([
                TextColumn::make('reference')
                    ->label(__('lunarpanel::order.fulfilments.columns.reference')),
                TextColumn::make('state')
                    ->label(__('lunarpanel::order.fulfilments.columns.state'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->color(fn ($state) => static::stateColor((string) $state)),
                TextColumn::make('items')
                    ->label(__('lunarpanel::order.fulfilments.columns.items'))
                    ->getStateUsing(fn (Fulfilment $record) => $record->lines
                        ->map(fn ($line) => $line->quantity.' × '.($line->orderLine?->description ?? '#'.$line->order_line_id))
                        ->implode(', ')),
                TextColumn::make('tracking_number')
                    ->label(__('lunarpanel::order.fulfilments.columns.tracking'))
                    ->placeholder('—')
                    ->url(fn (Fulfilment $record) => $record->tracking_url, shouldOpenInNewTab: true),
                TextColumn::make('shipped_at')
                    ->label(__('lunarpanel::order.fulfilments.columns.shipped_at'))
                    ->dateTime()
                    ->placeholder('—'),
            ])
            ->headerActions([
                $this->getCreateAction(),
            ])
            ->recordActions([
                $this->getShipAction(),
                $this->getSplitAction(),
                $this->getCancelAction(),
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

    /**
     * Outstanding (unfulfilled) quantity per physical order line.
     *
     * @return Collection<int, array{line: OrderLine, outstanding: int}>
     */
    protected function outstandingLines(): Collection
    {
        $quantity = new FulfilmentQuantity;

        return $this->record->physicalLines()->get()
            ->map(fn ($line) => [
                'line' => $line,
                'outstanding' => $line->quantity - $quantity->coveredQuantity($this->record, $line->id),
            ])
            ->filter(fn ($row) => $row['outstanding'] > 0)
            ->values();
    }

    protected function getCreateAction(): Action
    {
        return Action::make('create_fulfilment')
            ->label(__('lunarpanel::order.fulfilments.actions.create.label'))
            ->modalHeading(__('lunarpanel::order.fulfilments.actions.create.modal_heading'))
            ->icon('heroicon-o-plus')
            ->visible(fn () => CreateFulfilment::canRun($this->record))
            ->schema(function () {
                $lines = $this->outstandingLines();

                if ($lines->isEmpty()) {
                    return [];
                }

                return $lines->map(fn ($row) => TextInput::make('qty_'.$row['line']->id)
                    ->label($row['line']->description)
                    ->helperText(__('lunarpanel::order.fulfilments.fields.outstanding', ['count' => $row['outstanding']]))
                    ->numeric()
                    ->minValue(0)
                    ->maxValue($row['outstanding'])
                    ->default(0)
                )->all();
            })
            ->action(function (array $data) {
                $lines = collect($data)
                    ->filter(fn ($qty) => (int) $qty > 0)
                    ->mapWithKeys(fn ($qty, $key) => [(int) str_replace('qty_', '', $key) => (int) $qty])
                    ->all();

                $this->runFulfilmentAction(
                    fn () => Fulfilments::create($this->record, $lines),
                    'create',
                );
            });
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

    protected function getCancelAction(): Action
    {
        return Action::make('cancel')
            ->label(__('lunarpanel::order.fulfilments.actions.cancel.label'))
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->requiresConfirmation()
            ->visible(fn (Fulfilment $record) => CancelFulfilment::canRun($record))
            ->action(fn (Fulfilment $record) => $this->runFulfilmentAction(
                fn () => Fulfilments::cancel($record),
                'cancel',
            ));
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
