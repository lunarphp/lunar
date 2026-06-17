<?php

namespace Lunar\Admin\Filament\Resources\OrderResource\Pages\Components;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Lang;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Lunar\Core\Actions\Fulfilment\ChangeFulfilmentLocation;
use Lunar\Core\Actions\Fulfilment\HoldFulfilment;
use Lunar\Core\Actions\Fulfilment\MergeFulfilments;
use Lunar\Core\Actions\Fulfilment\ReleaseFulfilment;
use Lunar\Core\Actions\Fulfilment\SplitFulfilment;
use Lunar\Core\Enums\FulfilmentStateCategory;
use Lunar\Core\Exceptions\FulfilmentException;
use Lunar\Core\Facades\Carriers;
use Lunar\Core\Facades\HoldReasons;
use Lunar\Core\Models\Fulfilment;
use Lunar\Core\Models\FulfilmentLine;
use Lunar\Core\Models\FulfilmentTracking;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\Order;
use Lunar\Core\States\Fulfilment\FulfilmentState;
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
                    ->hiddenLabel()
                    ->addActionLabel(__('lunarpanel::order.fulfilments.actions.add_tracking.label'))
                    ->schema($this->trackingFields())
                    ->itemLabel(fn (?int $index): string => __('lunarpanel::order.fulfilments.fields.tracking_item', [
                        'number' => ($index ?? 0) + 1,
                    ]))
                    ->defaultItems(1)
                    ->reorderable(false),
            ])
            ->action(fn (array $arguments, array $data) => $this->run(
                fn () => $this->findFulfilment($arguments)->ship($data['tracking'] ?? []),
                'ship',
            ));
    }

    /**
     * The no-tracking terminal action for methods that don't carry tracking
     * (collection → "Mark collected", digital → "Mark fulfilled"). Routes
     * through the `fulfil()` verb, so the agent/API path is identical.
     */
    public function fulfilAction(): Action
    {
        return Action::make('fulfil')
            ->label(fn (array $arguments) => $this->fulfilLabel($this->findFulfilment($arguments)))
            ->modalHeading(fn (array $arguments) => $this->fulfilLabel($this->findFulfilment($arguments)))
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->action(fn (array $arguments) => $this->run(
                fn () => $this->findFulfilment($arguments)->fulfil(),
                'fulfil',
            ));
    }

    /**
     * The terminal action label for a method — a per-method override (e.g.
     * collection → "Mark collected") falling back to the generic label.
     */
    protected function fulfilLabel(Fulfilment $fulfilment): string
    {
        $key = 'lunarpanel::order.fulfilments.actions.fulfil.labels.'.$fulfilment->method;

        return Lang::has($key)
            ? __($key)
            : __('lunarpanel::order.fulfilments.actions.fulfil.label');
    }

    /**
     * The handed-over timestamp label for a parcel — a per-method override
     * (shipped at / collected at / provisioned at) falling back to a generic
     * "fulfilled at". Public so the card can render it off `shipped_at`.
     */
    public function handedOverLabel(Fulfilment $fulfilment): string
    {
        $key = 'lunarpanel::order.fulfilments.columns.handed_over.'.$fulfilment->method;

        return Lang::has($key)
            ? __($key)
            : __('lunarpanel::order.fulfilments.columns.handed_over_default');
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
                fn () => $this->findFulfilment($arguments)->addTracking($data),
                'add_tracking',
            ));
    }

    public function removeTrackingAction(): Action
    {
        return Action::make('removeTracking')
            ->label(__('lunarpanel::order.fulfilments.actions.remove_tracking.label'))
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->action(fn (array $arguments) => $this->run(
                fn () => $this->findTracking($arguments)->remove(),
                'remove_tracking',
            ));
    }

    /**
     * Resolve a tracking row, scoped to this order's fulfilments so it can't
     * be addressed across orders.
     */
    protected function findTracking(array $arguments): FulfilmentTracking
    {
        /** @var FulfilmentTracking */
        return FulfilmentTracking::query()
            ->whereHas('fulfilment', fn ($query) => $query->where('order_id', $this->record->id))
            ->findOrFail($arguments['tracking']);
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
                        ->map(fn (string $label) => __($label))
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

        if ($this->run(fn () => $fulfilment->split($moves), 'split')) {
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
                && $f->method === $source->method
                && MergeFulfilments::isMergeable($f))
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

        if ($this->run(fn () => $source->moveLinesTo($target, $moves), 'merge')) {
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
                fn () => $this->findFulfilment($arguments)->changeLocation((int) $data['location']),
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
                fn () => $this->findFulfilment($arguments)->markReturned(),
                'return',
            ));
    }

    /**
     * Undo a mistaken return — moves the parcel back to its method's fulfilled
     * state (shipped / collected), keeping the handover (shipped_at + tracking)
     * intact. Only the return is reversed.
     */
    public function undoReturnAction(): Action
    {
        return Action::make('undoReturn')
            ->label(__('lunarpanel::order.fulfilments.actions.undo_return.label'))
            ->icon('heroicon-o-arrow-uturn-right')
            ->color('warning')
            ->requiresConfirmation()
            ->action(fn (array $arguments) => $this->run(
                function () use ($arguments) {
                    $fulfilment = $this->findFulfilment($arguments);

                    return $fulfilment->transition($fulfilment->method()->fulfilledState());
                },
                'undo_return',
            ));
    }

    public function holdAction(): Action
    {
        return Action::make('hold')
            ->label(__('lunarpanel::order.fulfilments.actions.hold.label'))
            ->modalHeading(__('lunarpanel::order.fulfilments.actions.hold.modal_heading'))
            ->modalWidth(Width::Medium)
            ->icon('heroicon-o-pause-circle')
            ->color('warning')
            ->schema([
                Select::make('reason')
                    ->label(__('lunarpanel::order.fulfilments.actions.hold.reason'))
                    ->options(HoldReasons::all())
                    ->native(false),
                Textarea::make('note')
                    ->label(__('lunarpanel::order.fulfilments.actions.hold.note')),
            ])
            ->action(fn (array $arguments, array $data) => $this->run(
                fn () => $this->findFulfilment($arguments)->hold($data['reason'] ?? null, $data['note'] ?? null),
                'hold',
            ));
    }

    public function releaseAction(): Action
    {
        return Action::make('release')
            ->label(__('lunarpanel::order.fulfilments.actions.release.label'))
            ->icon('heroicon-o-play-circle')
            ->color('success')
            ->requiresConfirmation()
            ->action(fn (array $arguments) => $this->run(
                fn () => $this->findFulfilment($arguments)->release(),
                'release',
            ));
    }

    /**
     * Cancel a progressed fulfilment — reverts it to its method's default state
     * so it returns to the unfulfilled pool and can be progressed again (e.g.
     * the wrong parcel was shipped). A deliberate, destructive correction, not a
     * menu step.
     */
    public function cancelFulfilmentAction(): Action
    {
        return Action::make('cancelFulfilment')
            ->label(__('lunarpanel::order.fulfilments.actions.cancel.label'))
            ->modalHeading(__('lunarpanel::order.fulfilments.actions.cancel.modal_heading'))
            ->modalDescription(__('lunarpanel::order.fulfilments.actions.cancel.description'))
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->requiresConfirmation()
            ->action(fn (array $arguments) => $this->run(
                function () use ($arguments) {
                    $fulfilment = $this->findFulfilment($arguments);

                    return $fulfilment->transition($fulfilment->method()->defaultState());
                },
                'cancel',
            ));
    }

    /**
     * The states the given fulfilment can currently move to (already filtered to
     * the parcel's method by the guarded graph), used to build the "Update
     * status" menu, with the action each target routes to precomputed. Excludes
     * reverting to the method's default state (that is the destructive "Cancel
     * fulfilment" action), any `Cancelled`-category target (programmatic only),
     * and — while on hold — any `Fulfilled`-category target.
     *
     * @return \Illuminate\Support\Collection<int, array{name: string, state: class-string<FulfilmentState>, label: string, category: string, action: string}>
     */
    public function statusTransitions(Fulfilment $fulfilment): \Illuminate\Support\Collection
    {
        $method = $fulfilment->method();
        $fulfilledState = $method->fulfilledState();
        $defaultState = $method->defaultState();

        return collect($fulfilment->state->transitionableStateInstances())
            ->reject(function (FulfilmentState $state) use ($fulfilment, $defaultState) {
                $category = $state->category();

                return $state::class === $defaultState
                    || $category === FulfilmentStateCategory::Cancelled
                    || ($fulfilment->isOnHold() && $category === FulfilmentStateCategory::Fulfilled);
            })
            ->map(fn (FulfilmentState $state) => [
                'name' => $state::$name,
                'state' => $state::class,
                'label' => $state->label(),
                'category' => $state->category()->name,
                'action' => match (true) {
                    $state::class === $fulfilledState => $method->usesTracking() ? 'ship' : 'fulfil',
                    $state->category() === FulfilmentStateCategory::Returned => 'return',
                    default => 'transition',
                },
            ])
            ->values();
    }

    /**
     * The "More actions" dropdown descriptors for a parcel — the built-in set
     * (split / merge / change location / add tracking / undo return / hold /
     * release / cancel), already gated for this parcel's method and state.
     *
     * Consumers reshape it (add a bespoke action, remove one that doesn't apply,
     * relabel) through the `extendFulfilmentActions` hook — every built-in
     * routes through a core verb, so an agent/API consumer and the admin share
     * one path. A consumer's action descriptor `wire`s to a Livewire/mounted
     * action they register on the component via the same extension mechanism.
     *
     * @return array<string, array{label: string, icon: string, color: ?string, wire: string}>
     */
    public function moreActions(Fulfilment $fulfilment): array
    {
        $id = $fulfilment->id;
        $method = $fulfilment->method();
        $category = $fulfilment->state->category();
        $actions = [];

        if (SplitFulfilment::canRun($fulfilment) && $fulfilment->lines->sum('quantity') > 1) {
            $actions['split'] = [
                'label' => __('lunarpanel::order.fulfilments.actions.split.label'),
                'icon' => 'heroicon-m-scissors',
                'color' => null,
                'wire' => "startSplit({$id})",
            ];
        }

        if (MergeFulfilments::isMergeable($fulfilment) && $this->mergeTargets($fulfilment)->isNotEmpty()) {
            $actions['merge'] = [
                'label' => __('lunarpanel::order.fulfilments.actions.merge.label'),
                'icon' => 'heroicon-m-arrows-pointing-in',
                'color' => null,
                'wire' => "startMerge({$id})",
            ];
        }

        if (ChangeFulfilmentLocation::canRun($fulfilment) && $this->locations->count() > 1) {
            $actions['changeLocation'] = [
                'label' => __('lunarpanel::order.fulfilments.actions.change_location.label'),
                'icon' => 'heroicon-m-map-pin',
                'color' => null,
                'wire' => "mountAction('changeLocation', { fulfilment: {$id} })",
            ];
        }

        if ($method->usesTracking() && $category === FulfilmentStateCategory::Fulfilled) {
            $actions['addTracking'] = [
                'label' => __('lunarpanel::order.fulfilments.actions.add_tracking.label'),
                'icon' => 'heroicon-m-truck',
                'color' => null,
                'wire' => "mountAction('addTracking', { fulfilment: {$id} })",
            ];
        }

        if ($category === FulfilmentStateCategory::Returned
            && $fulfilment->state->canTransitionTo($method->fulfilledState())) {
            $actions['undoReturn'] = [
                'label' => __('lunarpanel::order.fulfilments.actions.undo_return.label'),
                'icon' => 'heroicon-m-arrow-uturn-right',
                'color' => null,
                'wire' => "mountAction('undoReturn', { fulfilment: {$id} })",
            ];
        }

        if (HoldFulfilment::canRun($fulfilment)) {
            $actions['hold'] = [
                'label' => __('lunarpanel::order.fulfilments.actions.hold.label'),
                'icon' => 'heroicon-m-pause-circle',
                'color' => null,
                'wire' => "mountAction('hold', { fulfilment: {$id} })",
            ];
        }

        if (ReleaseFulfilment::canRun($fulfilment)) {
            $actions['release'] = [
                'label' => __('lunarpanel::order.fulfilments.actions.release.label'),
                'icon' => 'heroicon-m-play-circle',
                'color' => null,
                'wire' => "mountAction('release', { fulfilment: {$id} })",
            ];
        }

        // "Cancel" = revert a progressed parcel back to its default state.
        if ($fulfilment->state->canTransitionTo($method->defaultState())) {
            $actions['cancel'] = [
                'label' => __('lunarpanel::order.fulfilments.actions.cancel.label'),
                'icon' => 'heroicon-m-x-circle',
                'color' => 'danger',
                'wire' => "mountAction('cancelFulfilment', { fulfilment: {$id} })",
            ];
        }

        return $this->callLunarHook('extendFulfilmentActions', $actions, $fulfilment);
    }

    /**
     * Generic "move to state X" confirmation, used for every transition that
     * isn't `Shipped` (which has its own form-bearing action). The target state
     * is validated against what the fulfilment can currently transition to.
     */
    public function transitionAction(): Action
    {
        return Action::make('transition')
            ->requiresConfirmation()
            ->modalHeading(fn (array $arguments) => __('lunarpanel::order.fulfilments.actions.transition.modal_heading', [
                'status' => $this->resolveTransition($arguments)?->label() ?? '',
            ]))
            ->modalSubmitActionLabel(fn (array $arguments) => $this->resolveTransition($arguments)?->label())
            ->color(fn (array $arguments) => ($arguments['state'] ?? null) === 'cancelled' ? 'danger' : 'primary')
            ->action(function (array $arguments) {
                $target = $this->resolveTransition($arguments);

                if (! $target) {
                    return;
                }

                $this->run(
                    fn () => $this->findFulfilment($arguments)->transition($target::class),
                    'transition',
                );
            });
    }

    /**
     * Resolve the target state instance for a transition action from its
     * arguments, only matching states the fulfilment may currently move to.
     */
    protected function resolveTransition(array $arguments): ?FulfilmentState
    {
        return collect($this->findFulfilment($arguments)->state->transitionableStateInstances())
            ->first(fn (FulfilmentState $state) => $state::$name === ($arguments['state'] ?? null));
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

        unset($this->fulfilments);
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
