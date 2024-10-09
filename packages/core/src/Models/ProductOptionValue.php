<?php

namespace Lunar\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Lunar\Base\BaseModel;
use Lunar\Base\Casts\AsAttributeData;
use Lunar\Base\Traits\HasAttributes;
use Lunar\Base\Traits\HasMacros;
use Lunar\Base\Traits\HasMedia;
use Lunar\Base\Traits\HasTranslations;
use Lunar\Database\Factories\ProductOptionValueFactory;
use Spatie\MediaLibrary\HasMedia as SpatieHasMedia;

/**
 * @property int $id
 * @property int $product_option_id
 * @property string $name
 * @property int $position
 * @property ?array $attribute_data
 * @property ?\Illuminate\Support\Carbon $created_at
 * @property ?\Illuminate\Support\Carbon $updated_at
 */
class ProductOptionValue extends BaseModel implements Contracts\ProductOptionValue, SpatieHasMedia
{
    use HasAttributes;
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
        'name' => AsCollection::class,
        'attribute_data' => AsAttributeData::class,
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
        return $this->belongsTo(ProductOption::modelClass(), 'product_option_id');
    }

    public function variants(): BelongsToMany
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->belongsToMany(
            ProductVariant::modelClass(),
            "{$prefix}product_option_value_product_variant",
            'value_id',
            'variant_id',
        );
    }

    /**
     * Get the mapped attributes relation.
     */
    public function mappedAttributes(): MorphToMany
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->morphToMany(
            Attribute::class,
            'attributable',
            "{$prefix}attributables"
        )->withTimestamps();
    }
}
