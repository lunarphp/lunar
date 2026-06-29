<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Lunar\Core\Database\Factories\ProductOptionValueFactory;
use Lunar\Core\Models\Concerns\HasMacros;
use Lunar\Core\Models\Concerns\HasMedia;
use Lunar\Core\Models\Concerns\HasTranslations;
use Spatie\MediaLibrary\HasMedia as SpatieHasMedia;

/**
 * @property int $id
 * @property int $product_option_id
 * @property AsArrayObject $name
 * @property int $position
 * @property ?AsArrayObject $meta
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class ProductOptionValue extends Base implements Contracts\ProductOptionValue, SpatieHasMedia
{
    use HasFactory;
    use HasMacros;
    use HasMedia;
    use HasTranslations;

    /**
     * Define which attributes should be cast.
     *
     * @var array
     */
    protected $casts = [
        'name' => AsArrayObject::class,
        'meta' => AsArrayObject::class,
    ];

    /**
     * Define which attributes should be
     * protected from mass assignment.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return ProductOptionValueFactory::new();
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(ProductOption::class, 'product_option_id');
    }

    public function variants(): BelongsToMany
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->belongsToMany(
            ProductVariant::class,
            "{$prefix}product_option_value_product_variant",
            'value_id',
            'variant_id',
        );
    }
}
