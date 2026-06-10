<?php

namespace Lunar\Core\Actions\Fulfilment;

use Lunar\Core\Contracts\Actions\Fulfilment\ShipsFulfilment;
use Lunar\Core\Contracts\CarrierManifest;
use Lunar\Core\Exceptions\FulfilmentException;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Contracts\Fulfilment as FulfilmentContract;
use Lunar\Core\Models\Fulfilment;
use Lunar\Core\States\Fulfilment\Shipped;

/**
 * Transition a `Pending` / `InProgress` fulfilment to `Shipped`, stamping
 * `shipped_at` and recording any tracking references. The state change is
 * routed through the guarded `FulfilmentState` graph — an illegal transition
 * throws and the action is a no-op.
 */
class ShipFulfilment implements ShipsFulfilment
{
    public function __construct(
        protected CarrierManifest $carriers,
    ) {}

    /**
     * @param  array<string, mixed>|array<int, array<string, mixed>>  $tracking
     *                                                                           a single tracking entry, or a list of them
     */
    public function execute(FulfilmentContract $fulfilment, array $tracking = []): Fulfilment
    {
        /** @var Fulfilment $fulfilment */
        if ($fulfilment->isOnHold()) {
            throw new FulfilmentException(__('lunar::exceptions.fulfilment_on_hold'));
        }

        return DB::transaction(function () use ($fulfilment, $tracking) {
            $fulfilment->state->transitionTo(Shipped::class);

            $fulfilment->forceFill(['shipped_at' => now()])->save();

            foreach ($this->normaliseTracking($tracking) as $entry) {
                $fulfilment->trackings()->create($entry);
            }

            return $fulfilment->refresh();
        });
    }

    /**
     * Whether the fulfilment can be shipped, per the `FulfilmentState` graph.
     */
    public static function canRun(FulfilmentContract $fulfilment): bool
    {
        /** @var Fulfilment $fulfilment */
        return ! $fulfilment->isOnHold()
            && $fulfilment->state->canTransitionTo(Shipped::class);
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
