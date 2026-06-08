<?php

namespace Lunar\Admin\Filament\Resources\OrderResource\Pages\Components;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Lunar\Core\Actions\Fulfilment\MergeFulfilments;
use Lunar\Core\Actions\Fulfilment\SplitFulfilment;
use Lunar\Core\Exceptions\FulfilmentException;
use Lunar\Core\Facades\Carriers;
use Lunar\Core\Facades\Fulfilments;
use Lunar\Core\Models\Fulfilment;
use Lunar\Core\Models\FulfilmentLine;
use Lunar\Core\Models\Location;
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
     * The fulfilment currently in inline "split" mode, if any.
     */
    public ?int $splittingId = null;

    /**
     * Quantity to move out per order line while splitting, keyed by
     * order_line_id.
     *
     * @var array<int, int>
     */
    public array $splitQuantities = [];

    /**
     * The fulfilment currently in inline "merge" mode, if any.
     */
    public ?int $mergingId = null;

    /**
     * Quantity to merge out per order line, keyed by order_line_id.
     *
     * @var array<int, int>
     */
    public array $mergeQuantities = [];

    /**
     * Chosen destination fulfilment while merging (auto-set when there is only
     * one candidate).
     */
    public ?int $mergeTargetId = null;

    /**
     * @return Collection<int, Fulfilment>
     */
    #[Computed]
    public function fulfilments(): Collection
    {
        return $this->record->fulfilments()
            ->with(['location', 'trackings', 'lines.orderLine.purchasable'])
            ->orderBy('id')
            ->get();
    }

    /**
     * All locations, for the "change location" picker and gating.
     *
     * @return \Illuminate\Support\Collection<int, Location>
     */
    #[Computed]
    public function locations(): \Illuminate\Support\Collection
    {
        return Location::query()->orderBy('name')->get();
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
                Repeater::make('tracking')
                    ->label(__('lunarpanel::order.fulfilments.fields.tracking'))
                    ->addActionLabel(__('lunarpanel::order.fulfilments.actions.add_tracking.label'))
                    ->schema($this->trackingFields())
                    ->defaultItems(1)
                    ->reorderable(false),
            ])
            ->action(fn (array $arguments, array $data) => $this->run(
                fn () => Fulfilments::ship($this->findFulfilment($arguments), $data['tracking'] ?? []),
                'ship',
            ));
    }

    public function addTrackingAction(): Action
    {
        return Action::make('addTracking')
            ->label(__('lunarpanel::order.fulfilments.actions.add_tracking.label'))
            ->modalHeading(__('lunarpanel::order.fulfilments.actions.add_tracking.modal_heading'))
            ->modalWidth(Width::Medium)
            ->icon('heroicon-o-plus')
            ->schema($this->trackingFields())
            ->action(fn (array $arguments, array $data) => $this->run(
                fn () => Fulfilments::addTracking($this->findFulfilment($arguments), $data),
                'add_tracking',
            ));
    }

    /**
     * The fields describing a single tracking reference, shared by the ship
     * repeater and the add-tracking modal. Picking a registered carrier drives
     * the shipping method options and derives the tracking URL, so the URL only
     * needs entering by hand for the "custom" (no carrier) case. Laid out two
     * to a row to keep the form compact — the carrier and tracking number span
     * the full width when their paired field is hidden.
     *
     * @return array<int, Grid>
     */
    protected function trackingFields(): array
    {
        return [
            Grid::make(2)->schema([
                Select::make('carrier')
                    ->label(__('lunarpanel::order.fulfilments.fields.carrier'))
                    ->placeholder(__('lunarpanel::order.fulfilments.fields.carrier_custom'))
                    ->options(Carriers::all()->mapWithKeys(
                        fn ($carrier) => [$carrier->getKey() => $carrier->getName()]
                    )->all())
                    ->native(false)
                    ->live()
                    ->columnSpan(fn (Get $get) => filled($get('carrier')) ? 1 : 2),
                Select::make('shipping_method')
                    ->label(__('lunarpanel::order.fulfilments.fields.shipping_method'))
                    ->options(fn (Get $get) => collect(Carriers::get($get('carrier'))?->getServices() ?? [])
                        ->mapWithKeys(fn (string $service) => [$service => $service])
                        ->all())
                    ->native(false)
                    ->visible(fn (Get $get) => filled($get('carrier'))),
                TextInput::make('tracking_number')
                    ->label(__('lunarpanel::order.fulfilments.fields.tracking_number'))
                    ->columnSpan(fn (Get $get) => filled($get('carrier')) ? 2 : 1),
                TextInput::make('tracking_url')
                    ->label(__('lunarpanel::order.fulfilments.fields.tracking_url'))
                    ->helperText(__('lunarpanel::order.fulfilments.fields.tracking_url_help'))
                    ->url()
                    ->visible(fn (Get $get) => blank($get('carrier'))),
            ]),
        ];
    }

    /**
     * Enter inline split mode for a parcel: each line gets a "move out"
     * quantity input and the card's actions become Split / Cancel.
     */
    public function startSplit(int $fulfilmentId): void
    {
        $fulfilment = $this->findFulfilment(['fulfilment' => $fulfilmentId]);

        if (! SplitFulfilment::canRun($fulfilment)) {
            return;
        }

        $this->splittingId = $fulfilmentId;
        $this->splitQuantities = $fulfilment->lines
            ->mapWithKeys(fn ($line) => [$line->order_line_id => 0])
            ->all();
    }

    public function cancelSplit(): void
    {
        $this->splittingId = null;
        $this->splitQuantities = [];
    }

    public function confirmSplit(): void
    {
        if (! $this->splittingId) {
            return;
        }

        $fulfilment = $this->findFulfilment(['fulfilment' => $this->splittingId]);

        $moves = collect($this->splitQuantities)
            ->map(fn ($qty) => (int) $qty)
            ->filter(fn ($qty) => $qty > 0)
            ->all();

        if ($moves === []) {
            Notification::make()
                ->danger()
                ->title(__('lunarpanel::order.fulfilments.actions.split.empty'))
                ->send();

            return;
        }

        if ($this->run(fn () => Fulfilments::split($fulfilment, $moves), 'split')) {
            $this->cancelSplit();
        }
    }

    /**
     * Other pre-ship fulfilments on the order that the given one could merge
     * into. Public so the view can list the destination options.
     *
     * @return \Illuminate\Support\Collection<int, Fulfilment>
     */
    public function mergeTargets(Fulfilment $source): \Illuminate\Support\Collection
    {
        return $this->fulfilments
            ->filter(fn (Fulfilment $f) => $f->getKey() !== $source->getKey()
                && $f->location_id === $source->location_id
                && in_array($f->state::$name, MergeFulfilments::MERGEABLE_STATES, true))
            ->values();
    }

    /**
     * Enter inline merge mode: pick the items (and quantities) to move out,
     * and — when there's more than one candidate — which parcel to merge into.
     */
    public function startMerge(int $fulfilmentId): void
    {
        $fulfilment = $this->findFulfilment(['fulfilment' => $fulfilmentId]);
        $targets = $this->mergeTargets($fulfilment);

        if ($targets->isEmpty()) {
            return;
        }

        $this->mergingId = $fulfilmentId;
        $this->mergeQuantities = $fulfilment->lines
            ->mapWithKeys(fn ($line) => [$line->order_line_id => $line->quantity])
            ->all();
        // Default to the first candidate; the destination picker is only shown
        // when there's more than one to choose between.
        $this->mergeTargetId = $targets->first()->id;
    }

    public function cancelMerge(): void
    {
        $this->mergingId = null;
        $this->mergeQuantities = [];
        $this->mergeTargetId = null;
    }

    public function confirmMerge(): void
    {
        if (! $this->mergingId) {
            return;
        }

        $source = $this->findFulfilment(['fulfilment' => $this->mergingId]);
        $targets = $this->mergeTargets($source);

        $targetId = $this->mergeTargetId ?? $targets->first()?->id;
        $target = $targetId
            ? $this->record->fulfilments()->with('lines')->find($targetId)
            : null;

        $moves = collect($this->mergeQuantities)
            ->map(fn ($qty) => (int) $qty)
            ->filter(fn ($qty) => $qty > 0)
            ->all();

        if (! $target || $moves === []) {
            Notification::make()
                ->danger()
                ->title(__('lunarpanel::order.fulfilments.actions.merge.empty'))
                ->send();

            return;
        }

        if ($this->run(fn () => Fulfilments::move($source, $target, $moves), 'merge')) {
            $this->cancelMerge();
        }
    }

    public function changeLocationAction(): Action
    {
        return Action::make('changeLocation')
            ->label(__('lunarpanel::order.fulfilments.actions.change_location.label'))
            ->modalHeading(__('lunarpanel::order.fulfilments.actions.change_location.modal_heading'))
            ->modalWidth(Width::Medium)
            ->icon('heroicon-o-map-pin')
            ->schema(fn (array $arguments) => [
                Select::make('location')
                    ->label(__('lunarpanel::order.fulfilments.actions.change_location.field'))
                    ->options($this->locations->mapWithKeys(fn (Location $location) => [$location->id => $location->name])->all())
                    ->default($this->findFulfilment($arguments)->location_id)
                    ->selectablePlaceholder(false)
                    ->required(),
            ])
            ->action(fn (array $arguments, array $data) => $this->run(
                fn () => Fulfilments::changeLocation($this->findFulfilment($arguments), (int) $data['location']),
                'change_location',
            ));
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
    protected function run(callable $callback, string $key): bool
    {
        try {
            $callback();
        } catch (FulfilmentException|CouldNotPerformTransition $e) {
            Notification::make()
                ->danger()
                ->title($e->getMessage() ?: __('lunarpanel::order.fulfilments.actions.'.$key.'.notification.error'))
                ->send();

            return false;
        }

        Notification::make()
            ->success()
            ->title(__('lunarpanel::order.fulfilments.actions.'.$key.'.notification.success'))
            ->send();

        unset($this->fulfilments, $this->mergeableCount);
        $this->dispatch('fulfilments-updated');

        return true;
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
            ['label' => __('lunarpanel::order.fulfilments.fields.unit_price'), 'value' => $orderLine->format('unit_price')],
            ['label' => __('lunarpanel::order.fulfilments.fields.quantity'), 'value' => (string) $orderLine->quantity],
            ['label' => __('lunarpanel::order.fulfilments.fields.sub_total'), 'value' => $orderLine->format('sub_total')],
            ['label' => __('lunarpanel::order.fulfilments.fields.discount_total'), 'value' => $orderLine->format('discount_total')],
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
