<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Lunar\Core\Contracts\Actions\Fulfilment\RemovesFulfilmentTracking;
use Lunar\Core\Contracts\ShippingCarrier;
use Lunar\Core\Database\Factories\FulfilmentTrackingFactory;
use Lunar\Core\Facades\Carriers;
use Lunar\Core\Models\Concerns\HasMacros;
use Lunar\Core\Models\Concerns\LogsActivity;

/**
 * A tracking reference against a fulfilment — a fulfilment can carry several
 * (e.g. a shipment split across multiple boxes or carriers).
 *
 * @property int $id
 * @property int $fulfilment_id
 * @property ?string $carrier
 * @property ?string $shipping_method
 * @property ?string $tracking_number
 * @property ?string $tracking_url
 * @property ?array $meta
 * @property-read ?string $url
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class FulfilmentTracking extends Base implements Contracts\FulfilmentTracking
{
    use HasFactory;
    use HasMacros;
    use LogsActivity;

    /**
     * {@inheritDoc}
     */
    protected $guarded = [];

    /**
     * {@inheritDoc}
     */
    protected $casts = [
        'meta' => AsArrayObject::class,
    ];

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return FulfilmentTrackingFactory::new();
    }

    /**
     * Return the fulfilment relationship.
     */
    public function fulfilment(): BelongsTo
    {
        return $this->belongsTo(Fulfilment::class);
    }

    /**
     * Resolve the registered carrier for this tracking, if any.
     */
    public function carrier(): ?ShippingCarrier
    {
        return Carriers::get($this->carrier);
    }

    /**
     * The translated label for the selected shipping method, resolved through
     * the carrier's service catalogue and falling back to the stored value for
     * a custom (carrier-less) tracking reference.
     */
    public function shippingMethodLabel(): ?string
    {
        if (! $this->shipping_method) {
            return null;
        }

        $services = $this->carrier()?->getServices() ?? [];

        return (string) __($services[$this->shipping_method] ?? $this->shipping_method);
    }

    /**
     * Remove this tracking reference from its fulfilment — the swappable
     * seam, unlike a bare `delete()`.
     */
    public function remove(): void
    {
        app(RemovesFulfilmentTracking::class)->execute($this);
    }

    /**
     * The public tracking URL — an explicitly stored URL takes precedence,
     * otherwise it is derived from the carrier and tracking number.
     */
    protected function url(): Attribute
    {
        return Attribute::get(function (): ?string {
            if ($this->tracking_url) {
                return $this->tracking_url;
            }

            if (! $this->tracking_number) {
                return null;
            }

            return $this->carrier()?->getTrackingUrl($this->tracking_number);
        });
    }
}
