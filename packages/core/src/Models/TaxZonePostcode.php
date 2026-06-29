<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Lunar\Core\Database\Factories\TaxZonePostcodeFactory;
use Lunar\Core\Models\Concerns\HasMacros;

/**
 * @property int $id
 * @property ?int $tax_zone_id
 * @property ?int $country_id
 * @property string $postcode
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class TaxZonePostcode extends Base implements Contracts\TaxZonePostcode
{
    use HasFactory;
    use HasMacros;

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return TaxZonePostcodeFactory::new();
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
     * Return the country relation.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
