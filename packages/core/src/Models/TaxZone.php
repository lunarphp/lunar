<?php

namespace Lunar\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Lunar\Base\BaseModel;
use Lunar\Base\Traits\HasDefaultRecord;
use Lunar\Base\Traits\HasMacros;
use Lunar\Database\Factories\TaxZoneFactory;

/**
 * @property int $id
 * @property string $name
 * @property string $zone_type
 * @property string $price_display
 * @property bool $active
 * @property bool $default
 * @property ?\Illuminate\Support\Carbon $created_at
 * @property ?\Illuminate\Support\Carbon $updated_at
 */
class TaxZone extends BaseModel implements \Lunar\Models\Contracts\TaxZone
{
    use HasDefaultRecord;
    use HasFactory;
    use HasMacros;

    protected static function booted(): void
    {
        $handleDefaultFunction = fn (TaxZone $taxZone) => TaxZone::when(
            $taxZone->default,
            fn ($query) => $query->where('id', '!=', $taxZone->id)->update([
                'default' => false,
            ])
        );
        static::created($handleDefaultFunction);

        static::updated($handleDefaultFunction);
    }

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory(): TaxZoneFactory
    {
        return TaxZoneFactory::new();
    }

    /**
     * Define which attributes should be
     * protected from mass assignment.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Define the attribute casting.
     *
     * @var array
     */
    protected $casts = [
        'active' => 'boolean',
        'default' => 'boolean',
    ];

    /**
     * Return the countries relationship.
     */
    public function countries(): HasMany
    {
        return $this->hasMany(TaxZoneCountry::modelClass());
    }

    /**
     * Return the states relationship.
     */
    public function states(): HasMany
    {
        return $this->hasMany(TaxZoneState::modelClass());
    }

    /**
     * Return the postcodes relationship.
     */
    public function postcodes(): HasMany
    {
        return $this->hasMany(TaxZonePostcode::modelClass());
    }

    /**
     * Return the customer groups relationship.
     */
    public function customerGroups(): HasMany
    {
        return $this->hasMany(TaxZoneCustomerGroup::modelClass());
    }

    /**
     * Return the tax rates relationship.
     */
    public function taxRates(): HasMany
    {
        return $this->hasMany(TaxRate::modelClass());
    }

    /**
     * Return the tax amounts relationship.
     */
    public function taxAmounts(): HasManyThrough
    {
        return $this->hasManyThrough(TaxRateAmount::modelClass(), TaxRate::modelClass());
    }
}
