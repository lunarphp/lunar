<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Lunar\Core\Database\Factories\TaxZoneStateFactory;
use Lunar\Core\Models\Concerns\HasMacros;

/**
 * @property int $id
 * @property ?int $tax_zone_id
 * @property ?int $state_id
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class TaxZoneState extends Base
{
    use HasFactory;
    use HasMacros;

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return TaxZoneStateFactory::new();
    }

    /**
     * Define which attributes should be
     * protected from mass assignment.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Return the tax zone relation.
     */
    public function taxZone(): BelongsTo
    {
        return $this->belongsTo(TaxZone::class);
    }

    /**
     * Return the state relation.
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }
}
