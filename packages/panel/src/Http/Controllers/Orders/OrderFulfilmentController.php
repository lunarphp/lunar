<?php

namespace Lunar\Panel\Http\Controllers\Orders;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Lunar\Core\Actions\Fulfilment\ChangeFulfilmentLocation;
use Lunar\Core\Actions\Fulfilment\FulfilFulfilment;
use Lunar\Core\Actions\Fulfilment\HoldFulfilment;
use Lunar\Core\Actions\Fulfilment\MergeFulfilments;
use Lunar\Core\Actions\Fulfilment\ReleaseFulfilment;
use Lunar\Core\Actions\Fulfilment\ReturnFulfilment;
use Lunar\Core\Actions\Fulfilment\ShipFulfilment;
use Lunar\Core\Actions\Fulfilment\SplitFulfilment;
use Lunar\Core\Enums\FulfilmentStateCategory;
use Lunar\Core\Exceptions\FulfilmentException;
use Lunar\Core\Models\Fulfilment;
use Lunar\Core\Models\FulfilmentLine;
use Lunar\Core\Models\FulfilmentTracking;
use Lunar\Core\Models\Order;
use Lunar\Panel\Http\Requests\Orders\ChangeFulfilmentLocationRequest;
use Lunar\Panel\Http\Requests\Orders\FulfilmentTrackingRequest;
use Lunar\Panel\Http\Requests\Orders\HoldFulfilmentRequest;
use Lunar\Panel\Http\Requests\Orders\MergeFulfilmentRequest;
use Lunar\Panel\Http\Requests\Orders\ShipFulfilmentRequest;
use Lunar\Panel\Http\Requests\Orders\SplitFulfilmentRequest;
use Lunar\Panel\Http\Requests\Orders\TransitionFulfilmentRequest;
use Lunar\Panel\Support\FulfilmentTransitions;
use Spatie\ModelStates\Exceptions\CouldNotPerformTransition;

/**
 * Fulfilment mutations, each delegating to a core fulfilment verb behind its
 * guard. Fulfilments are never created here — the initial fulfilment is
 * created at order placement and admins split it down or merge it back.
 */
class OrderFulfilmentController
{
    public function ship(ShipFulfilmentRequest $request, Order $order, Fulfilment $fulfilment): RedirectResponse
    {
        abort_unless(ShipFulfilment::canRun($fulfilment), 403);

        return $this->run(
            fn () => $fulfilment->ship($request->input('tracking', []), $request->boolean('notify', true)),
            'panel::orders.flash_fulfilment_shipped',
        );
    }

    /**
     * The no-tracking terminal verb — mark collected / mark fulfilled for
     * methods that don't carry tracking. Tracking methods ship instead.
     */
    public function fulfil(Request $request, Order $order, Fulfilment $fulfilment): RedirectResponse
    {
        abort_unless(! $fulfilment->method()->usesTracking() && FulfilFulfilment::canRun($fulfilment), 403);

        return $this->run(
            fn () => $fulfilment->fulfil($request->boolean('notify', true)),
            'panel::orders.flash_fulfilment_fulfilled',
        );
    }

    /**
     * A plain intermediate transition (in progress, ready for collection, …).
     * The target must be one the panel offers for this fulfilment — terminal
     * moves route through their dedicated endpoints.
     */
    public function transition(TransitionFulfilmentRequest $request, Order $order, Fulfilment $fulfilment): RedirectResponse
    {
        $target = FulfilmentTransitions::for($fulfilment)
            ->first(fn (array $transition) => $transition['via'] === 'transition'
                && $transition['name'] === $request->string('state')->value());

        abort_unless((bool) $target, 403);

        return $this->run(
            fn () => $fulfilment->transition($target['state']::class, $request->boolean('notify', true)),
            'panel::orders.flash_fulfilment_transitioned',
        );
    }

    public function split(SplitFulfilmentRequest $request, Order $order, Fulfilment $fulfilment): RedirectResponse
    {
        abort_unless(SplitFulfilment::canRun($fulfilment), 403);

        return $this->run(
            fn () => $fulfilment->split($request->moves()),
            'panel::orders.flash_fulfilment_split',
        );
    }

    /**
     * Fold this fulfilment into another by moving every allocated quantity
     * across — an emptied source is removed, matching the admin's merge.
     */
    public function merge(MergeFulfilmentRequest $request, Order $order, Fulfilment $fulfilment): RedirectResponse
    {
        abort_unless(MergeFulfilments::isMergeable($fulfilment), 403);

        $target = $order->fulfilments()
            ->whereKeyNot($fulfilment->id)
            ->where('location_id', $fulfilment->location_id)
            ->where('method', $fulfilment->method)
            ->find($request->integer('target_id'));

        if (! $target || ! MergeFulfilments::isMergeable($target)) {
            throw ValidationException::withMessages([
                'target_id' => __('panel::orders.merge_target_invalid'),
            ]);
        }

        $moves = $fulfilment->lines
            ->mapWithKeys(fn (FulfilmentLine $line) => [$line->order_line_id => $line->quantity])
            ->all();

        return $this->run(
            fn () => $fulfilment->moveLinesTo($target, $moves),
            'panel::orders.flash_fulfilment_merged',
        );
    }

    public function markReturned(Request $request, Order $order, Fulfilment $fulfilment): RedirectResponse
    {
        abort_unless(ReturnFulfilment::canRun($fulfilment), 403);

        return $this->run(
            fn () => $fulfilment->markReturned($request->boolean('notify', true)),
            'panel::orders.flash_fulfilment_returned',
        );
    }

    /**
     * Undo a mistaken return — back to the method's fulfilled state, keeping
     * the handover (shipped_at + tracking) intact.
     */
    public function undoReturn(Request $request, Order $order, Fulfilment $fulfilment): RedirectResponse
    {
        abort_unless(
            $fulfilment->state->category() === FulfilmentStateCategory::Returned
                && $fulfilment->state->canTransitionTo($fulfilment->method()->fulfilledState()),
            403,
        );

        return $this->run(
            fn () => $fulfilment->transition($fulfilment->method()->fulfilledState(), $request->boolean('notify', true)),
            'panel::orders.flash_fulfilment_return_undone',
        );
    }

    public function hold(HoldFulfilmentRequest $request, Order $order, Fulfilment $fulfilment): RedirectResponse
    {
        abort_unless(HoldFulfilment::canRun($fulfilment), 403);

        return $this->run(
            fn () => $fulfilment->hold($request->input('reason'), $request->input('note')),
            'panel::orders.flash_fulfilment_held',
        );
    }

    public function release(Order $order, Fulfilment $fulfilment): RedirectResponse
    {
        abort_unless(ReleaseFulfilment::canRun($fulfilment), 403);

        return $this->run(
            fn () => $fulfilment->release(),
            'panel::orders.flash_fulfilment_released',
        );
    }

    /**
     * The destructive correction: revert a progressed fulfilment to its
     * method's default state, clearing the handover and returning its items
     * to the unfulfilled pool.
     */
    public function cancel(Order $order, Fulfilment $fulfilment): RedirectResponse
    {
        abort_unless($fulfilment->state->canTransitionTo($fulfilment->method()->defaultState()), 403);

        return $this->run(
            fn () => $fulfilment->transition($fulfilment->method()->defaultState(), notify: false),
            'panel::orders.flash_fulfilment_cancelled',
        );
    }

    public function updateLocation(ChangeFulfilmentLocationRequest $request, Order $order, Fulfilment $fulfilment): RedirectResponse
    {
        abort_unless(ChangeFulfilmentLocation::canRun($fulfilment), 403);

        return $this->run(
            fn () => $fulfilment->changeLocation($request->integer('location_id')),
            'panel::orders.flash_fulfilment_location_changed',
        );
    }

    public function storeTracking(FulfilmentTrackingRequest $request, Order $order, Fulfilment $fulfilment): RedirectResponse
    {
        abort_unless(
            $fulfilment->method()->usesTracking()
                && $fulfilment->state->category() === FulfilmentStateCategory::Fulfilled,
            403,
        );

        return $this->run(
            fn () => $fulfilment->addTracking($request->validated()),
            'panel::orders.flash_tracking_added',
        );
    }

    public function destroyTracking(Order $order, Fulfilment $fulfilment, FulfilmentTracking $tracking): RedirectResponse
    {
        return $this->run(
            fn () => $tracking->remove(),
            'panel::orders.flash_tracking_removed',
        );
    }

    /**
     * Run a core fulfilment verb, translating a domain/transition failure into
     * a flash error rather than a 500.
     */
    protected function run(callable $callback, string $successKey): RedirectResponse
    {
        try {
            $callback();
        } catch (FulfilmentException|CouldNotPerformTransition $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', __($successKey));
    }
}
