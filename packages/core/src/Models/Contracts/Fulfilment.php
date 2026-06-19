<?php

namespace Lunar\Core\Models\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Lunar\Core\Contracts\FulfilmentMethod;
use Lunar\Core\Models\FulfilmentTracking;

interface Fulfilment
{
    /**
     * Resolve the registered fulfilment method that owns this fulfilment's flow.
     */
    public function method(): FulfilmentMethod;

    /**
     * Return the order relationship.
     */
    public function order(): BelongsTo;

    /**
     * Return the location relationship.
     */
    public function location(): BelongsTo;

    /**
     * Return the fulfilment lines relationship.
     */
    public function lines(): HasMany;

    /**
     * Return the tracking references relationship.
     */
    public function trackings(): HasMany;

    /**
     * Whether the fulfilment is currently on hold (blocked from shipping).
     */
    public function isOnHold(): bool;

    /**
     * The human-readable label for the current hold reason, resolved from the
     * configured reason list (falls back to the stored key).
     */
    public function holdReasonLabel(): ?string;

    /**
     * Mark the fulfilment shipped, stamping `shipped_at` and recording the
     * given tracking entries. Pass `$notify: false` to suppress the customer
     * notification this state change would otherwise trigger.
     *
     * @param  array<int|string, mixed>  $tracking  a single tracking entry or a list of them
     */
    public function ship(array $tracking = [], bool $notify = true): \Lunar\Core\Models\Fulfilment;

    /**
     * Advance the fulfilment to its method's canonical "done" state with no
     * tracking (collection → collected, digital → provisioned, …). Pass
     * `$notify: false` to suppress the customer notification this state change
     * would otherwise trigger.
     */
    public function fulfil(bool $notify = true): \Lunar\Core\Models\Fulfilment;

    /**
     * Split quantities out of this pre-ship fulfilment into a new fulfilment.
     * Returns the new fulfilment.
     *
     * @param  array<int|string, int>  $moves  [order_line_id => quantity to move out]
     */
    public function split(array $moves): \Lunar\Core\Models\Fulfilment;

    /**
     * Absorb the given pre-ship fulfilments into this one. Returns this
     * fulfilment (the target), refreshed.
     *
     * @param  Collection<int, Fulfilment>  $sources
     */
    public function merge(Collection $sources): \Lunar\Core\Models\Fulfilment;

    /**
     * Move selected line quantities from this pre-ship fulfilment into
     * another on the same order. Returns the target.
     *
     * @param  array<int|string, int>  $moves  [order_line_id => quantity]
     */
    public function moveLinesTo(Fulfilment $to, array $moves): \Lunar\Core\Models\Fulfilment;

    /**
     * Cancel the fulfilment, returning its quantities to the order's
     * unfulfilled pool.
     */
    public function cancel(): \Lunar\Core\Models\Fulfilment;

    /**
     * Mark a shipped fulfilment as returned. Pass `$notify: false` to suppress
     * the customer notification this state change would otherwise trigger.
     */
    public function markReturned(bool $notify = true): \Lunar\Core\Models\Fulfilment;

    /**
     * Perform a plain guarded state transition (moves that carry no extra
     * behaviour — use the dedicated verbs for ship/cancel/return). Pass
     * `$notify: false` to suppress the customer notification this state change
     * would otherwise trigger.
     *
     * @param  class-string  $state
     */
    public function transition(string $state, bool $notify = true): \Lunar\Core\Models\Fulfilment;

    /**
     * Put the fulfilment on hold, blocking it from shipping.
     */
    public function hold(?string $reason = null, ?string $note = null): \Lunar\Core\Models\Fulfilment;

    /**
     * Release the fulfilment from hold.
     */
    public function release(): \Lunar\Core\Models\Fulfilment;

    /**
     * Move the fulfilment to another location.
     */
    public function changeLocation(int $locationId): \Lunar\Core\Models\Fulfilment;

    /**
     * Append a tracking reference to the fulfilment.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function addTracking(array $attributes): FulfilmentTracking;
}
