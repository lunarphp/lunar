<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Lunar\Core\Contracts\Actions\Fulfilment\AddsFulfilmentTracking;
use Lunar\Core\Contracts\Actions\Fulfilment\CancelsFulfilment;
use Lunar\Core\Contracts\Actions\Fulfilment\ChangesFulfilmentLocation;
use Lunar\Core\Contracts\Actions\Fulfilment\HoldsFulfilment;
use Lunar\Core\Contracts\Actions\Fulfilment\MergesFulfilments;
use Lunar\Core\Contracts\Actions\Fulfilment\MovesFulfilmentLines;
use Lunar\Core\Contracts\Actions\Fulfilment\ReleasesFulfilment;
use Lunar\Core\Contracts\Actions\Fulfilment\ReturnsFulfilment;
use Lunar\Core\Contracts\Actions\Fulfilment\ShipsFulfilment;
use Lunar\Core\Contracts\Actions\Fulfilment\SplitsFulfilment;
use Lunar\Core\Contracts\Actions\Fulfilment\TransitionsFulfilment;
use Lunar\Core\Database\Factories\FulfilmentFactory;
use Lunar\Core\Models\Concerns\HasMacros;
use Lunar\Core\Models\Concerns\LogsActivity;
use Lunar\Core\States\Fulfilment\FulfilmentState;
use Spatie\ModelStates\HasStates;

/**
 * @property int $id
 * @property int $order_id
 * @property int $location_id
 * @property ?string $reference
 * @property FulfilmentState $state
 * @property ?string $notes
 * @property ?array $meta
 * @property ?Carbon $shipped_at
 * @property ?Carbon $held_at
 * @property ?string $hold_reason
 * @property ?string $hold_note
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class Fulfilment extends Base implements Contracts\Fulfilment
{
    use HasFactory;
    use HasMacros;
    use HasStates;
    use LogsActivity;

    /**
     * {@inheritDoc}
     */
    protected $guarded = [];

    /**
     * {@inheritDoc}
     */
    protected $casts = [
        'state' => FulfilmentState::class,
        'shipped_at' => 'datetime',
        'held_at' => 'datetime',
        'meta' => AsArrayObject::class,
    ];

    /**
     * Whether the fulfilment is currently on hold (blocked from shipping).
     */
    public function isOnHold(): bool
    {
        return ! blank($this->held_at);
    }

    /**
     * The human-readable label for the current hold reason, resolved from the
     * configured reason list (falls back to the stored key).
     */
    public function holdReasonLabel(): ?string
    {
        if (blank($this->hold_reason)) {
            return null;
        }

        return config('lunar.fulfilment.hold_reasons.'.$this->hold_reason, $this->hold_reason);
    }

    /**
     * Limit the query to fulfilments currently on hold.
     */
    public function scopeOnHold(Builder $query): Builder
    {
        return $query->whereNotNull('held_at');
    }

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return FulfilmentFactory::new();
    }

    /**
     * Return the order relationship.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::modelClass());
    }

    /**
     * Return the location relationship.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::modelClass());
    }

    /**
     * Return the fulfilment lines relationship.
     */
    public function lines(): HasMany
    {
        return $this->hasMany(FulfilmentLine::modelClass());
    }

    /**
     * Return the tracking references relationship.
     */
    public function trackings(): HasMany
    {
        return $this->hasMany(FulfilmentTracking::modelClass());
    }

    /**
     * Mark the fulfilment shipped, stamping `shipped_at` and recording the
     * given tracking entries (spec 0029 verb for `ShipFulfilment`).
     *
     * @param  array<int|string, mixed>  $tracking  a single tracking entry or a list of them
     */
    public function ship(array $tracking = []): Fulfilment
    {
        return app(ShipsFulfilment::class)->execute($this, $tracking);
    }

    /**
     * Split quantities out of this pre-ship fulfilment into a new parcel.
     * Returns the new fulfilment.
     *
     * @param  array<int|string, int>  $moves  [order_line_id => quantity to move out]
     */
    public function split(array $moves): Fulfilment
    {
        return app(SplitsFulfilment::class)->execute($this, $moves);
    }

    /**
     * Absorb the given pre-ship fulfilments into this one. Returns this
     * fulfilment (the target), refreshed.
     *
     * @param  Collection<int, Contracts\Fulfilment>  $sources
     */
    public function merge(Collection $sources): Fulfilment
    {
        return app(MergesFulfilments::class)->execute($this, $sources);
    }

    /**
     * Move selected line quantities from this pre-ship fulfilment into
     * another on the same order. Returns the target.
     *
     * @param  array<int|string, int>  $moves  [order_line_id => quantity]
     */
    public function moveLinesTo(Contracts\Fulfilment $to, array $moves): Fulfilment
    {
        return app(MovesFulfilmentLines::class)->execute($this, $to, $moves);
    }

    /**
     * Cancel the fulfilment, returning its quantities to the order's
     * unfulfilled pool.
     */
    public function cancel(): Fulfilment
    {
        return app(CancelsFulfilment::class)->execute($this);
    }

    /**
     * Mark a shipped fulfilment as returned.
     */
    public function markReturned(): Fulfilment
    {
        return app(ReturnsFulfilment::class)->execute($this);
    }

    /**
     * Perform a plain guarded state transition (moves that carry no extra
     * behaviour — use the dedicated verbs for ship/cancel/return).
     *
     * @param  class-string  $state
     */
    public function transition(string $state): Fulfilment
    {
        return app(TransitionsFulfilment::class)->execute($this, $state);
    }

    /**
     * Put the fulfilment on hold, blocking it from shipping.
     */
    public function hold(?string $reason = null, ?string $note = null): Fulfilment
    {
        return app(HoldsFulfilment::class)->execute($this, $reason, $note);
    }

    /**
     * Release the fulfilment from hold.
     */
    public function release(): Fulfilment
    {
        return app(ReleasesFulfilment::class)->execute($this);
    }

    /**
     * Move the fulfilment to another location.
     */
    public function changeLocation(int $locationId): Fulfilment
    {
        return app(ChangesFulfilmentLocation::class)->execute($this, $locationId);
    }

    /**
     * Append a tracking reference to the fulfilment.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function addTracking(array $attributes): FulfilmentTracking
    {
        return app(AddsFulfilmentTracking::class)->execute($this, $attributes);
    }
}
