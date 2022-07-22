<?php

namespace GetCandy\Models;

use GetCandy\Base\BaseModel;
use GetCandy\Base\Traits\HasMacros;
use GetCandy\Base\Traits\HasTranslations;
use GetCandy\Database\Factories\ProductFeatureValueFactory;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ProductFeatureValue.
 *
 * @property string $name
 * @property int $position
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class ProductFeatureValue extends BaseModel
{
    use HasFactory;
    use HasTranslations;
    use HasMacros;

    /**
     * Define which attributes should be cast.
     *
     * @var array
     */
    protected $casts = [
        'name' => AsCollection::class,
    ];

    /**
     * Return a new factory instance for the model.
     *
     * @return \GetCandy\Database\Factories\ProductFeatureValueFactory
     */
    protected static function newFactory(): ProductFeatureValueFactory
    {
        return ProductFeatureValueFactory::new();
    }

    /**
     * Define which attributes should be
     * protected from mass assignment.
     *
     * @var array
     */
    protected $guarded = [];

    protected function setNameAttribute($value)
    {
        $this->attributes['name'] = json_encode($value);
    }

    public function getNameAttribute($value)
    {
        return json_decode($value);
    }

    /**
     * Get the product feature this value belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function productFeature(): BelongsTo
    {
        return $this->belongsTo(ProductFeature::class, 'product_feature_id');
    }
}
