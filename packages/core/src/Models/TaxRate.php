<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Lunar\Core\Database\Factories\TaxRateFactory;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Concerns\HasMacros;
use Lunar\Core\Models\Concerns\HasPublicId;
use Lunar\Core\Models\Concerns\LogsActivity;

/**
 * @property int $id
 * @property string $public_id
 * @property ?int $tax_zone_id
 * @property bool $priority
 * @property string $name
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class TaxRate extends Base
{
    use HasFactory;
    use HasMacros;
    use HasPublicId;
    use LogsActivity;

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return TaxRateFactory::new();
    }

    protected static function booted(): void
    {
        static::deleting(function (self $taxRate) {
            DB::beginTransaction();
            $taxRate->taxRateAmounts()->delete();
            DB::commit();
        });
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
     * Return the tax rate amounts relation.
     */
    public function taxRateAmounts(): HasMany
    {
        return $this->hasMany(TaxRateAmount::class);
    }
}
