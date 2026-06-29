<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Lunar\Core\Database\Factories\RegionFactory;
use Lunar\Core\Models\Concerns\HasDefaultRecord;
use Lunar\Core\Models\Concerns\HasMacros;

/**
 * @property int $id
 * @property string $name
 * @property string $handle
 * @property int $channel_id
 * @property int $currency_id
 * @property int $language_id
 * @property ?int $tax_zone_id
 * @property ?bool $prices_inc_tax
 * @property bool $default
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class Region extends Base implements Contracts\Region
{
    use HasDefaultRecord;
    use HasFactory;
    use HasMacros;

    protected $guarded = [];

    protected $casts = [
        'prices_inc_tax' => 'boolean',
    ];

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return RegionFactory::new();
    }

    /**
     * Mutator for formatting the handle to a slug.
     */
    public function setHandleAttribute(?string $val): void
    {
        $this->attributes['handle'] = Str::slug($val);
    }

    /**
     * Whether the storefront shows prices inclusive of tax in this region.
     * This is a display preference only — it does not affect how prices are
     * stored. Falls back to the global storage default when not set.
     */
    public function displaysPricesIncludingTax(): bool
    {
        return $this->prices_inc_tax ?? prices_inc_tax();
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function taxZone(): BelongsTo
    {
        return $this->belongsTo(TaxZone::class);
    }

    public function countries(): BelongsToMany
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->belongsToMany(Country::class, "{$prefix}country_region");
    }
}
