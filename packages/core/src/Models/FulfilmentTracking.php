<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Lunar\Core\Database\Factories\FulfilmentTrackingFactory;
use Lunar\Core\Models\Concerns\HasMacros;
use Lunar\Core\Models\Concerns\LogsActivity;

/**
 * A tracking reference against a fulfilment — a parcel can carry several
 * (e.g. a shipment split across multiple boxes or carriers).
 *
 * @property int $id
 * @property int $fulfilment_id
 * @property ?string $tracking_number
 * @property ?string $tracking_url
 * @property ?string $shipping_method
 * @property ?array $meta
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
        return $this->belongsTo(Fulfilment::modelClass());
    }
}
