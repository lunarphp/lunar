<?php

namespace Lunar\Core\Actions\Fulfilment;

use Lunar\Core\Contracts\Actions\Fulfilment\ShipsFulfilment;
use Lunar\Core\Contracts\Actions\Fulfilment\TransitionsFulfilment;
use Lunar\Core\Contracts\CarrierManifest;
use Lunar\Core\Exceptions\FulfilmentException;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Contracts\Fulfilment as FulfilmentContract;
use Lunar\Core\Models\Fulfilment;

/**
 * Mark a fulfilment shipped: advance it to its method's `fulfilledState()`,
 * stamping the handed-over timestamp and recording any tracking references.
 *
 * The tracking-bearing terminal — only valid for methods that carry tracking;
 * collection/digital use `fulfil()` instead, and calling `ship()` on a
 * non-tracking method throws. The transition is delegated to
 * `TransitionFulfilment` (so it routes through the guarded, per-method
 * `FulfilmentState` graph and the timestamp is stamped by category); an illegal
 * transition throws and the action is a no-op.
 */
class ShipFulfilment implements ShipsFulfilment
{
    public function __construct(
        protected CarrierManifest $carriers,
        protected TransitionsFulfilment $transitionFulfilment,
    ) {}

    /**
     * @param  array<string, mixed>|array<int, array<string, mixed>>  $tracking
     *                                                                           a single tracking entry, or a list of them
     */
    public function execute(FulfilmentContract $fulfilment, array $tracking = [], bool $notify = true): Fulfilment
    {
        /** @var Fulfilment $fulfilment */
        if ($fulfilment->isOnHold()) {
            throw new FulfilmentException(__('lunar::exceptions.fulfilment_on_hold'));
        }

        if (! $fulfilment->method()->usesTracking()) {
            throw new FulfilmentException(__('lunar::exceptions.fulfilment_method_no_tracking', [
                'method' => $fulfilment->method()->getLabel(),
            ]));
        }

        return DB::transaction(function () use ($fulfilment, $tracking, $notify) {
            // Validate tracking first so a bad number aborts before the
            // transition; the surrounding transaction rolls it all back.
            $entries = $this->normaliseTracking($tracking);

            $this->transitionFulfilment->execute($fulfilment, $fulfilment->method()->fulfilledState(), $notify);

            foreach ($entries as $entry) {
                $fulfilment->trackings()->create($entry);
            }

            return $fulfilment->refresh();
        });
    }

    /**
     * Whether the fulfilment can be shipped — its method carries tracking and
     * its state can advance to that method's fulfilled state.
     */
    public static function canRun(FulfilmentContract $fulfilment): bool
    {
        /** @var Fulfilment $fulfilment */
        return ! $fulfilment->isOnHold()
            && $fulfilment->method()->usesTracking()
            && $fulfilment->state->canTransitionTo($fulfilment->method()->fulfilledState());
    }

    /**
     * Accept either a single tracking entry or a list, keep only known fields,
     * and drop entirely-empty entries.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function normaliseTracking(array $tracking): array
    {
        if ($tracking === []) {
            return [];
        }

        $entries = array_is_list($tracking) ? $tracking : [$tracking];

        return collect($entries)
            ->map(fn (array $entry) => array_intersect_key(
                $entry,
                array_flip(['carrier', 'tracking_number', 'tracking_url', 'shipping_method']),
            ))
            ->filter(fn (array $entry) => filled($entry['tracking_number'] ?? null)
                || filled($entry['tracking_url'] ?? null)
                || filled($entry['shipping_method'] ?? null))
            ->each(fn (array $entry) => $this->validateTrackingNumber($entry))
            ->values()
            ->all();
    }

    /**
     * Reject a tracking number that does not match the carrier's expected
     * format, when both a carrier and a number are present.
     *
     * @param  array<string, mixed>  $entry
     */
    protected function validateTrackingNumber(array $entry): void
    {
        $number = $entry['tracking_number'] ?? null;
        $carrier = $this->carriers->get($entry['carrier'] ?? null);

        if (filled($number) && $carrier && ! $carrier->validateTrackingNumber($number)) {
            throw new FulfilmentException(__('lunar::exceptions.fulfilment_tracking_invalid_number', [
                'carrier' => $carrier->getName(),
            ]));
        }
    }
}
