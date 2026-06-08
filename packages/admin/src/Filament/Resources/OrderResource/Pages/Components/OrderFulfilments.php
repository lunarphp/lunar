<?php

namespace Lunar\Admin\Filament\Resources\OrderResource\Pages\Components;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Lunar\Core\Actions\Fulfilment\MergeFulfilments;
use Lunar\Core\Exceptions\FulfilmentException;
use Lunar\Core\Facades\Fulfilments;
use Lunar\Core\Models\Fulfilment;
use Lunar\Core\Models\FulfilmentLine;
use Lunar\Core\Models\Order;
use Lunar\Filament\Support\Concerns\CallsHooks;
use Spatie\ModelStates\Exceptions\CouldNotPerformTransition;

/**
 * The fulfilments panel on the order screen — a bespoke card list (no table
 * chrome) with mounted Filament actions for the Shopify-style split-down
 * workflow: split / merge pre-ship parcels, ship, and return.
 */
class OrderFulfilments extends Component implements HasActions, HasForms
{
    use CallsHooks;
    use InteractsWithActions;
    use InteractsWithForms;

    #[Locked]
    public Order $record;

    /**
     * @return Collection<int, Fulfilment>
     */
    #[Computed]
    public function fulfilments(): Collection
    {
        return $this->record->fulfilments()
            ->with('lines.orderLine.purchasable')
            ->orderBy('id')
            ->get();
    }

    /**
     * Number of pre-ship parcels — merge is only offered when there is more
     * than one to combine.
     */
    #[Computed]
    public function mergeableCount(): int
    {
        return $this->fulfilments
            ->filter(fn (Fulfilment $f) => in_array($f->state::$name, MergeFulfilments::MERGEABLE_STATES, true))
            ->count();
    }

    protected function findFulfilment(array $arguments): Fulfilment
    {
        /** @var Fulfilment */
        return $this->record->fulfilments()->with('lines.orderLine')->findOrFail($arguments['fulfilment']);
    }

    public function shipAction(): Action
    {
        return Action::make('ship')
            ->label(__('lunarpanel::order.fulfilments.actions.ship.label'))
            ->modalHeading(__('lunarpanel::order.fulfilments.actions.ship.modal_heading'))
            ->icon('heroicon-o-truck')
            ->color('success')
            ->schema([
                TextInput::make('tracking_number')
                    ->label(__('lunarpanel::order.fulfilments.fields.tracking_number')),
                TextInput::make('tracking_url')
                    ->label(__('lunarpanel::order.fulfilments.fields.tracking_url'))
                    ->url(),
                TextInput::make('shipping_method')
                    ->label(__('lunarpanel::order.fulfilments.fields.shipping_method')),
            ])
            ->action(fn (array $arguments, array $data) => $this->run(
                fn () => Fulfilments::ship($this->findFulfilment($arguments), array_filter($data)),
                'ship',
            ));
    }

    public function splitAction(): Action
    {
        return Action::make('split')
            ->label(__('lunarpanel::order.fulfilments.actions.split.label'))
            ->modalHeading(__('lunarpanel::order.fulfilments.actions.split.modal_heading'))
            ->icon('heroicon-o-scissors')
            ->schema(fn (array $arguments) => $this->findFulfilment($arguments)->lines->map(
                fn ($line) => TextInput::make('qty_'.$line->order_line_id)
                    ->label($line->orderLine?->description ?? '#'.$line->order_line_id)
                    ->helperText(__('lunarpanel::order.fulfilments.fields.outstanding', ['count' => $line->quantity]))
                    ->numeric()
                    ->minValue(0)
                    ->maxValue($line->quantity)
                    ->default(0),
            )->all())
            ->action(function (array $arguments, array $data) {
                $moves = collect($data)
                    ->filter(fn ($qty) => (int) $qty > 0)
                    ->mapWithKeys(fn ($qty, $key) => [(int) str_replace('qty_', '', $key) => (int) $qty])
                    ->all();

                $this->run(fn () => Fulfilments::split($this->findFulfilment($arguments), $moves), 'split');
            });
    }

    public function mergeAction(): Action
    {
        return Action::make('merge')
            ->label(__('lunarpanel::order.fulfilments.actions.merge.label'))
            ->modalHeading(__('lunarpanel::order.fulfilments.actions.merge.modal_heading'))
            ->icon('heroicon-o-arrows-pointing-in')
            ->schema(function (array $arguments) {
                $target = $this->findFulfilment($arguments);

                $options = $this->fulfilments
                    ->filter(fn (Fulfilment $f) => $f->getKey() !== $target->getKey()
                        && in_array($f->state::$name, MergeFulfilments::MERGEABLE_STATES, true))
                    ->mapWithKeys(fn (Fulfilment $f) => [$f->id => $f->reference])
                    ->all();

                return [
                    CheckboxList::make('sources')
                        ->label(__('lunarpanel::order.fulfilments.actions.merge.label'))
                        ->options($options)
                        ->required(),
                ];
            })
            ->action(function (array $arguments, array $data) {
                $target = $this->findFulfilment($arguments);

                /** @var Collection<int, Fulfilment> $sources */
                $sources = $this->record->fulfilments()
                    ->with('lines')
                    ->whereIn('id', $data['sources'] ?? [])
                    ->get();

                $this->run(fn () => Fulfilments::merge($target, $sources), 'merge');
            });
    }

    public function returnAction(): Action
    {
        return Action::make('return')
            ->label(__('lunarpanel::order.fulfilments.actions.return.label'))
            ->icon('heroicon-o-arrow-uturn-left')
            ->color('warning')
            ->requiresConfirmation()
            ->action(fn (array $arguments) => $this->run(
                fn () => Fulfilments::return($this->findFulfilment($arguments)),
                'return',
            ));
    }

    /**
     * Run a fulfilment operation, surfacing domain/transition failures as a
     * notification rather than an unhandled exception, and refreshing the list.
     */
    protected function run(callable $callback, string $key): void
    {
        try {
            $callback();
        } catch (FulfilmentException|CouldNotPerformTransition $e) {
            Notification::make()
                ->danger()
                ->title($e->getMessage() ?: __('lunarpanel::order.fulfilments.actions.'.$key.'.notification.error'))
                ->send();

            return;
        }

        Notification::make()
            ->success()
            ->title(__('lunarpanel::order.fulfilments.actions.'.$key.'.notification.success'))
            ->send();

        unset($this->fulfilments, $this->mergeableCount);
        $this->dispatch('fulfilments-updated');
    }

    /**
     * The rows shown in a line's expandable detail panel. Developers can add,
     * remove, or reshape rows via the `extendFulfilmentLineDetails` hook:
     *
     *   LunarPanel::extensions([
     *       OrderFulfilments::class => new class {
     *           public function extendFulfilmentLineDetails(array $rows, FulfilmentLine $line): array {
     *               $rows[] = ['label' => 'SKU', 'value' => $line->orderLine?->identifier];
     *               return $rows;
     *           }
     *       },
     *   ]);
     *
     * @return list<array{label: string, value: string, highlight?: bool}>
     */
    public function lineDetails(FulfilmentLine $line): array
    {
        $orderLine = $line->orderLine;

        if (! $orderLine) {
            return [];
        }

        $rows = [
            ['label' => __('lunarpanel::order.fulfilments.fields.unit_price'), 'value' => $orderLine->format('unit_price', decimalPlaces: 4)],
            ['label' => __('lunarpanel::order.fulfilments.fields.quantity'), 'value' => (string) $line->quantity],
        ];

        foreach ($orderLine->tax_breakdown?->amounts ?? [] as $tax) {
            $rows[] = ['label' => $tax->description, 'value' => $tax->price->format()];
        }

        $rows[] = ['label' => __('lunarpanel::order.fulfilments.fields.total'), 'value' => $orderLine->format('total'), 'highlight' => true];

        return $this->callLunarHook('extendFulfilmentLineDetails', $rows, $line);
    }

    public function render()
    {
        return view('lunarpanel::resources.order-resource.components.order-fulfilments');
    }
}
