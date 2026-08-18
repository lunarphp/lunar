<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Lunar\Core\Database\Factories\TaxClassFactory;
use Lunar\Core\Models\Concerns\HasDefaultRecord;
use Lunar\Core\Models\Concerns\HasMacros;
use Lunar\Core\Models\Concerns\HasPublicId;
use Lunar\Core\Models\Concerns\LogsActivity;

/**
 * @property int $id
 * @property string $public_id
 * @property string $name
 * @property bool $default
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class TaxClass extends Base
{
    use HasDefaultRecord;
    use HasFactory;
    use HasMacros;
    use HasPublicId;
    use LogsActivity;

    public static function booted()
    {
        static::updated(function ($taxClass) {
            if ($taxClass->default) {
                TaxClass::whereDefault(true)->where('id', '!=', $taxClass->id)->update([
                    'default' => false,
                ]);
            }
        });

        static::created(function ($taxClass) {
            if ($taxClass->default) {
                TaxClass::whereDefault(true)->where('id', '!=', $taxClass->id)->update([
                    'default' => false,
                ]);
            }
        });
    }

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return TaxClassFactory::new();
    }

    /**
     * Define which attributes should be
     * protected from mass assignment.
     *
     * @var array
     */
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'default' => 'boolean',
        ];
    }

    public function taxRateAmounts(): HasMany
    {
        return $this->hasMany(TaxRateAmount::class);
    }

    public function productVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }
}
