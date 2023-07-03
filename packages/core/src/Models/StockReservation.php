<?php

namespace Lunar\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Lunar\Base\BaseModel;
use Lunar\Base\Traits\HasMacros;
use Lunar\Database\Factories\StockReservationFactory;

/**
 * @property int $id
 * @property int $stockable_id
 * @property string $stockable_type
 * @property int $quantity
 * @property \Illuminate\Support\Carbon $expires_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class StockReservation extends BaseModel
{
    use HasFactory;
    use HasMacros;

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory(): StockReservationFactory
    {
        return StockReservationFactory::new();
    }

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'meta' => AsArrayObject::class,
    ];

    /**
     * Define which attributes should be
     * protected from mass assignment.
     *
     * @var array
     */
    protected $guarded = [];

    public function stockable(): MorphTo
    {
        return $this->morphTo();
    }
}
