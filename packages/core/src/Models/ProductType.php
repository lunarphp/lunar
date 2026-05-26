<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Carbon;
use Lunar\Core\Concerns\HasAttributes;
use Lunar\Core\Concerns\HasMacros;
use Lunar\Core\Concerns\LogsActivity;
use Lunar\Core\Database\Factories\ProductTypeFactory;

/**
 * @property int $id
 * @property string $name
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class ProductType extends Base implements Contracts\ProductType
{
    use HasAttributes;
    use HasFactory;
    use HasMacros;
    use LogsActivity;

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return ProductTypeFactory::new();
    }

    /**
     * Define which attributes should be
     * protected from mass assignment.
     *
     * @var array
     */
    protected $guarded = [];

    public function mappedAttributes(): MorphToMany
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->morphToMany(
            Attribute::modelClass(),
            'attributable',
            "{$prefix}attributables"
        )->withTimestamps();
    }

    public function productAttributes(): MorphToMany
    {
        return $this->mappedAttributes()->whereAttributeType(
            Product::morphName()
        );
    }

    public function variantAttributes(): MorphToMany
    {
        return $this->mappedAttributes()->whereAttributeType(
            ProductVariant::morphName()
        );
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::modelClass());
    }
}
